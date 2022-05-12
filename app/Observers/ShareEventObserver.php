<?php

namespace App\Observers;

use App\Enums\PlantActivityTypesEnum;
use App\Enums\ShareEventStatesEnum;
use App\Models\ShareEvent;
use App\Notifications\InvitationReceivedNotification;
use App\Notifications\InviteReceivedNotification;
use App\Notifications\RequestReceivedNotification;

class ShareEventObserver
{
    public function created(ShareEvent $shareEvent)
    {
        $shareEvent->isShareType()
            ? $shareEvent->respondent->notify(
                new InvitationReceivedNotification(
                    $shareEvent,
                    $shareEvent->initiator,
                    $shareEvent->respondent->full_name
                )
            )
            : $shareEvent->respondent->notify(
                new RequestReceivedNotification(
                    $shareEvent,
                    $shareEvent->initiator,
                    $shareEvent->respondent->full_name
                )
            );
    }

    public function updated(ShareEvent $shareEvent)
    {
        if ($shareEvent->isDirty('state') && $shareEvent->state === ShareEventStatesEnum::CONFIRMATION_WAITING) {
            PlantActivityTypesEnum::LOVE()->is($shareEvent->type)
                ? $shareEvent->initiator->notify(new InviteReceivedNotification($shareEvent, $shareEvent->respondent, $shareEvent->initiator->full_name))
                : $shareEvent->respondent->notify(new InviteReceivedNotification($shareEvent, $shareEvent->initiator, $shareEvent->respondent->full_name));
        }
    }
}
