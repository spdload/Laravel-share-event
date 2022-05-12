<?php

namespace App\Models;

use App\DTO\ShareEventData;
use App\DTO\ShareMessageData;
use App\Enums\MeetingPlaceTypesEnum;
use App\Enums\PlantActivityTypesEnum;
use App\Enums\PlantVarietySizesEnum;
use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;
use App\Enums\ShareMethodsEnum;
use App\Events\MessageSent;
use App\Models\Scope\ShareEventScopesTrait;
use App\Models\ShareEvent\ShareEventRelations;
use App\Notifications\RescheduledNotification;
use App\States\ShareEvent\Canceled;
use App\States\ShareEvent\Completed;
use App\States\ShareEvent\ConfirmationWaiting;
use App\States\ShareEvent\Confirmed;
use App\States\ShareEvent\Connected;
use App\States\ShareEvent\Initial;
use App\States\ShareEvent\Rescheduling;
use App\States\ShareEvent\ReviewWaiting;
use App\States\ShareEvent\ShareEventState;
use App\Transitions\ShareEvent\InitialToConnected;
use App\Transitions\ShareEvent\ToRescheduling;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Spatie\ModelStates\HasStates;

/**
 * @property  ShareEventState $state
 */
class ShareEvent extends Model
{
    use HasStates;
    use ShareEventRelations;
    use ShareEventScopesTrait;

    protected $fillable = [
        'type',
        'count',
        'size',
        'comment',
        'meeting_place',
        'meeting_place_type',
        'share_method',
        'share_date',
        'is_feedback_read_by_respondent',
        'is_feedback_read_by_initiator',
        'is_changed_by_initiator',
        'is_changed_by_respondent',
    ];

    protected $dates = [
        'share_date',
    ];

    protected $attributes = [
        'is_changed_by_initiator' => false,
        'is_changed_by_respondent' => false,
        'is_feedback_read_by_initiator' => false,
        'is_feedback_read_by_respondent' => false,
    ];

    /**
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     */
    protected function registerStates(): void
    {
        $this->addState('state', ShareEventState::class)
            ->default(Initial::class)
            ->allowTransitions([
                [Initial::class, Connected::class, InitialToConnected::class],
                [ConfirmationWaiting::class, Confirmed::class],
                [Confirmed::class, ReviewWaiting::class],
                [ReviewWaiting::class, Completed::class],
            ])->allowTransition([
                Initial::class,
                Connected::class,
                ConfirmationWaiting::class,
                Confirmed::class,
                Rescheduling::class,
            ], Canceled::class)
            ->allowTransition([
                ConfirmationWaiting::class,
                Confirmed::class,
            ], Rescheduling::class, ToRescheduling::class)
            ->allowTransition([
                Connected::class,
                Rescheduling::class,
                Confirmed::class,
            ], ConfirmationWaiting::class);
    }

    public function reschedule(User $initiator)
    {
        $sharePartner = $this->getSharePartner($initiator->getKey());
        $this->state->transitionTo(Rescheduling::class);
        $sharePartner->notify(new RescheduledNotification($this, $initiator, $sharePartner->full_name));

        $user = $this->getActiveType($initiator->getKey()) === PlantActivityTypesEnum::LOVE
            ? $initiator
            : $sharePartner;

        $shareMessage = ShareMessage::createForShareEvent($this, new ShareMessageData([
            'message' => __('share-event.reschedule_message'),
            'user' => $user,
            'images' => [],
        ]));

        broadcast(new MessageSent(
            $user,
            $shareMessage,
            $this->getKey()
        ))->toOthers();
    }

    public function invite(User $initiator)
    {
        $this->changeByPartner($initiator->getKey());
        $this->state->transitionTo(ConfirmationWaiting::class);

        $shareMessage = ShareMessage::createForShareEvent($this, new ShareMessageData([
            'message' => __('share-event.invite_message'),
            'user' => $initiator,
            'images' => [],
        ]));

        broadcast(new MessageSent(
            $initiator,
            $shareMessage,
            $this->getKey()
        ))->toOthers();
    }

    public function isReviewMissing(int $userId, bool $isPartner = false): bool
    {
        if ($this->state != ShareEventStatesEnum::REVIEW_WAITING) {
            return false;
        }

        return $isPartner ? $this->reviews->where('user_id', '<>', $userId)->isEmpty()
            : $this->reviews->where('user_id', $userId)->isEmpty();
    }

    public static function createBetweenUsersWithAttributes(ShareEventData $shareEventData): self
    {
        $shareEvent = self::make($shareEventData->toArray());

        $shareEvent->initiator()->associate($shareEventData->initiator);
        $shareEvent->respondent()->associate($shareEventData->respondent);
        $shareEvent->plantVariety()->associate($shareEventData->plantVariety);

        $shareEvent->save();

        return $shareEvent;
    }

    public function isDiscussable(): bool
    {
        return ! $this->state->isOneOf(
            Initial::class,
            Canceled::class,
            ReviewWaiting::class,
            Completed::class,
        );
    }

    public function getMeetingPlaceAttribute(?string $value): ?string
    {
        if ($this->meeting_place_type === MeetingPlaceTypesEnum::MY_ADDRESS) {
            return $this->type === PlantActivityTypesEnum::LOVE
                ? $this->respondent->address
                : $this->initiator->address;
        }

        if ($this->meeting_place_type === MeetingPlaceTypesEnum::THEIR_ADDRESS) {
            return $this->type === PlantActivityTypesEnum::LOVE
                ? $this->initiator->address
                : $this->respondent->address;
        }

        return $value;
    }

    public function getLocalShareDateAtAttribute(): ?string
    {
        return $this->share_date
            ? Carbon::createFromFormat('Y-m-d H:i:s', $this->share_date)
                ->setTimezone(auth()->user()->timezone ?? 'UTC')
                ->format('Y-m-d H:i:s')
            : null;
    }

    public function getSharePartner(int $userId): User
    {
        return $this->initiator->id === $userId
            ? $this->respondent
            : $this->initiator;
    }

    public function isAvailableForReview(int $reviewerId): bool
    {
        return $this->state->isOneOf(
            ReviewWaiting::class,
        ) && $this->isReviewMissing($reviewerId);
    }

    public function getPartnerReview(int $userId)
    {
        return $this->reviews->where('user_id', $this->getSharePartner($userId)->id)->first();
    }

    public function transitionToCompleted(): void
    {
        $this->state->transitionTo(Completed::class);
    }

    public function isShareType(): bool
    {
        return PlantActivityTypesEnum::SHARE()->is($this->type);
    }

    public function createFeedback(User $user, string $type, $additionalInfo = null): ShareEventFeedback
    {
        return $this->feedback()->save(ShareEventFeedback::make([
            'type' => $type,
            'additional_information' => $additionalInfo,
        ])->user()->associate($user));
    }

    public function hasFeedback(User $user): bool
    {
        return $this->feedback()->where('user_id', $user->id)->exists();
    }

    public function isReviewRead(int $userId, $partner = false): bool
    {
        return $partner
            ? $this->reviews->where('user_id', '<>', $userId)
                ->where('is_read', true)->isNotEmpty()

            : $this->reviews->where('user_id', $userId)
                ->where('is_read', true)->isNotEmpty();
    }

    public function getPlantSize(): ?string
    {
        if (! $this->size) {
            return null;
        }

        return PlantVarietySizesEnum::labels()[$this->size];
    }

    public function getActiveType(int $userId): string
    {
        if ($this->initiator->id === $userId) {
            return $this->type;
        }

        return $this->type === PlantActivityTypesEnum::LOVE
            ? PlantActivityTypesEnum::SHARE
            : PlantActivityTypesEnum::LOVE;
    }

    public function getFullShareDate(): ?string
    {
        if (! $this->share_date) {
            return null;
        }

        return $this->share_date->format('l, H:i A, F d, Y');
    }

    public function isFeedbackRead(int $userId): bool
    {
        return $this->initiator->id === $userId
            ? $this->is_feedback_read_by_initiator
            : $this->is_feedback_read_by_respondent;
    }

    public function isChanged(int $userId): bool
    {
        return $this->initiator->id === $userId
            ? $this->is_changed_by_initiator
            : $this->is_changed_by_respondent;
    }

    public function changeByPartner(int $userId): void
    {
        $this->initiator->id === $userId
            ? $this->update(['is_changed_by_respondent' => true])
            : $this->update(['is_changed_by_initiator' => true]);
    }

    public function readFeedbackBy(int $userId): void
    {
        if ($this->state->getClientState($userId) === ShareEventClientStatesEnum::REVIEW_WAITING) {
            $this->updateFeedbackReadState($userId);

            return;
        }
        if ($this->state->getClientState($userId) === ShareEventClientStatesEnum::REVIEW_RECEIVED
            && ! $this->isFeedbackRead($userId)
        ) {
            $this->updateFeedbackReadState($userId);
        }
    }

    protected function updateFeedbackReadState(int $userId): void
    {
        $this->initiator->id === $userId
            ? $this->update(['is_feedback_read_by_initiator' => true])
            : $this->update(['is_feedback_read_by_respondent' => true]);
    }

    public static function needFeedback(int $userId, bool $needFeedback): bool
    {
        return $needFeedback ? self::countOfEndedForUser($userId) >= 3 : false;
    }

    private static function countOfEndedForUser(int $userId): int
    {
        return self::where(function (Builder $query) use ($userId) {
            $query->where('initiator_id', $userId)
                ->orWhere('respondent_id', $userId);
        })->whereIn('state', [
            ShareEventStatesEnum::REVIEW_WAITING,
            ShareEventStatesEnum::COMPLETED,
        ])->count();
    }

    public function getWorkType(int $userId, string $userName)
    {
        if (! $this->share_method) {
            return;
        }

        if ($this->getActiveType($userId) === PlantActivityTypesEnum::SHARE) {
            if ($this->share_method === ShareMethodsEnum::I_WILL_SUPERVISE) {
                return __('share-method.i_will_supervise_share', ['name' => $userName]);
            }
            if ($this->share_method === ShareMethodsEnum::THEY_WILL_PICK_IT_UP) {
                return __('share-method.they_will_pick_it_up_share', ['name' => $userName]);
            }
        }

        if ($this->getActiveType($userId) === PlantActivityTypesEnum::LOVE) {
            if ($this->share_method === ShareMethodsEnum::I_WILL_SUPERVISE) {
                return __('share-method.i_will_supervise_love', ['name' => $userName]);
            }
            if ($this->share_method === ShareMethodsEnum::THEY_WILL_PICK_IT_UP) {
                return __('share-method.they_will_pick_it_up_love', ['name' => $userName]);
            }
        }

        return __('share-method.work_together');
    }
}
