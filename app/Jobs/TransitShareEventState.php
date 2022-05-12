<?php

namespace App\Jobs;

use App\Enums\ShareEventStatesEnum;
use App\Models\ShareEvent;
use App\Notifications\ThanksForShareEventsNotification;
use App\States\ShareEvent\ReviewWaiting;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class TransitShareEventState implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public function handle()
    {
        ShareEvent::where('state', ShareEventStatesEnum::CONFIRMED)
            ->each(function (ShareEvent $shareEvent) {
                if ($shareEvent->share_date <= Carbon::now($shareEvent->respondent->timezone)) {
                    $shareEvent->respondent
                        ->notify(new ThanksForShareEventsNotification($shareEvent, $shareEvent->respondent->full_name, $shareEvent->initiator));
                    $shareEvent->state->transitionTo(ReviewWaiting::class);
                }
                if ($shareEvent->share_date <= Carbon::now($shareEvent->initiator->timezone)) {
                    $shareEvent->initiator
                        ->notify(new ThanksForShareEventsNotification($shareEvent, $shareEvent->initiator->full_name, $shareEvent->respondent));
                }
            });
    }
}
