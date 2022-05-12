<?php

namespace App\Models\Scope;

use App\Enums\PlantActivityTypesEnum;
use App\Enums\ShareEventStatesEnum;
use App\Models\ShareEvent;
use Illuminate\Database\Eloquent\Builder;

/**
 * Trait ShareEventScopesTrait.
 *
 * @mixin ShareEvent
 */
trait ShareEventScopesTrait
{
    public function scopeJustUpdated(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::LOVE)
                ->where('is_changed_by_initiator', '=', true);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::SHARE)
                ->where('is_changed_by_respondent', '=', true);
        });
    }

    public function scopeJustUpdatedShare(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::SHARE)
                ->where('is_changed_by_initiator', '=', true);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::LOVE)
                ->where('is_changed_by_respondent', '=', true);
        });
    }

    public function scopeRequestSent(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::INITIAL)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        });
    }

    public function scopeInvitationSent(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::INITIAL)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        });
    }

    public function scopeNewRequest(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::INITIAL)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        });
    }

    public function scopeNewInvitation(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::INITIAL)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        });
    }

    public function scopeConnected(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONNECTED)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONNECTED)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        });
    }

    public function scopeConnectedShare(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONNECTED)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONNECTED)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        });
    }

    public function scopeConfirmationWaiting(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONFIRMATION_WAITING)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONFIRMATION_WAITING)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        });
    }

    public function scopeReplyWaiting(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONFIRMATION_WAITING)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONFIRMATION_WAITING)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        });
    }

    public function scopeComingRightUp(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONFIRMED);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::CONFIRMED);
        });
    }

    public function scopeRescheduling(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::RESCHEDULING);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('state', '=', ShareEventStatesEnum::RESCHEDULING);
        });
    }

    public function scopeReviewWaiting(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('state', '=', ShareEventStatesEnum::REVIEW_WAITING)
                ->where('initiator_id', '=', $userId)
                ->doesntHave('reviews');
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('state', '=', ShareEventStatesEnum::REVIEW_WAITING)
                ->where('respondent_id', '=', $userId)
                ->doesntHave('reviews');
        });
    }

    public function scopeReviewSent(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('state', '=', ShareEventStatesEnum::REVIEW_WAITING)
                ->has('reviews', '=', 1)
                ->whereHas('reviews', function (Builder $query) use ($userId) {
                    $query->where('user_id', $userId);
                });
        });
    }

    public function scopeReviewReceived(Builder $query, int $userId)
    {
        $query->orWhere(function (Builder $q) use ($userId) {
            $q->where('state', '=', ShareEventStatesEnum::REVIEW_WAITING)
                ->WhereHas('reviews', function (Builder $query) use ($userId) {
                    $query->where('user_id', '<>', $userId);
                });
        });
    }

    public function scopeCompleted(Builder $query)
    {
        $query->where('state', '=', ShareEventStatesEnum::COMPLETED);
    }

    public function scopeLove(Builder $query, int $userId)
    {
        $query->where(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        });
    }

    public function scopeShare(Builder $query, int $userId)
    {
        $query->where(function (Builder $q) use ($userId) {
            $q->where('initiator_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::SHARE);
        })->orWhere(function (Builder $q) use ($userId) {
            $q->where('respondent_id', '=', $userId)
                ->where('type', '=', PlantActivityTypesEnum::LOVE);
        });
    }
}
