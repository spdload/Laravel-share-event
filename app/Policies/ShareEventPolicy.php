<?php

namespace App\Policies;

use App\Models\ShareEvent;
use App\Models\User;
use App\States\ShareEvent\Completed;
use App\States\ShareEvent\ConfirmationWaiting;
use App\States\ShareEvent\ReviewWaiting;
use Illuminate\Auth\Access\HandlesAuthorization;

class ShareEventPolicy
{
    use HandlesAuthorization;

    public function viewAny(): bool
    {
        return true;
    }

    public function view(User $user, ShareEvent $shareEvent): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->access($user, $shareEvent);
    }

    public function update(User $user, ShareEvent $shareEvent): bool
    {
        if ($user->isAdmin()) {
            return true;
        }

        return $this->access($user, $shareEvent);
    }

    public function create(): bool
    {
        return true;
    }

    public function access(User $user, ShareEvent $shareEvent): bool
    {
        return $shareEvent->initiator_id === $user->id
            || $shareEvent->respondent_id === $user->id;
    }

    public function createInvite(User $user, ShareEvent $shareEvent): bool
    {
        return $user->isPlantGiver($shareEvent)
            && in_array(ConfirmationWaiting::$name, $shareEvent->state->transitionableStates());
    }

    public function createFeedback(User $user, ShareEvent $shareEvent): bool
    {
        return ($shareEvent->initiator_id === $user->id
            || $shareEvent->respondent_id === $user->id)
            && ($shareEvent->state->is(ReviewWaiting::class)
            && ! $shareEvent->hasFeedback($user));
    }

    public function delete(User $user, ShareEvent $shareEvent)
    {
        return $this->access($user, $shareEvent) && $shareEvent->state->is(Completed::class);
    }
}
