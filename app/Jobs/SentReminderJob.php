<?php

namespace App\Jobs;

use App\Models\ShareEvent;
use App\Notifications\TwentyFourHoursBeforeSharingNotification;
use App\Notifications\TwoHoursBeforeSharingNotification;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SentReminderJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public ShareEvent $shareEvent;

    public function __construct(ShareEvent $shareEvent)
    {
        $this->shareEvent = $shareEvent;
    }

    public function handle(): void
    {
        $shareDate = Carbon::make($this->shareEvent->share_date);
        $hoursDiffInitiator = $shareDate->diffInHours(Carbon::now($this->shareEvent->initiator->timezone));
        $hoursDiffRespondent = $shareDate->diffInHours(Carbon::now($this->shareEvent->respondent->timezone));

        if ($hoursDiffInitiator === 2 || $hoursDiffInitiator === 1) {
            $this->shareEvent->initiator->notify(
                new TwoHoursBeforeSharingNotification(
                    $this->shareEvent,
                    $this->shareEvent->respondent,
                    $this->shareEvent->initiator->full_name
                )
            );
        }
        if ($hoursDiffRespondent === 2 || $hoursDiffRespondent === 1) {
            $this->shareEvent->respondent->notify(
                new TwoHoursBeforeSharingNotification(
                    $this->shareEvent,
                    $this->shareEvent->initiator,
                    $this->shareEvent->respondent->full_name
                )
            );
        }

        if ($hoursDiffInitiator === 24 || $hoursDiffInitiator === 23) {
            $this->shareEvent->initiator->notify(
                new TwentyFourHoursBeforeSharingNotification(
                    $this->shareEvent,
                    $this->shareEvent->respondent,
                    $this->shareEvent->initiator->full_name
                )
            );
        }
        if ($hoursDiffRespondent === 24 || $hoursDiffRespondent === 23) {
            $this->shareEvent->respondent->notify(
                new TwentyFourHoursBeforeSharingNotification(
                    $this->shareEvent,
                    $this->shareEvent->initiator,
                    $this->shareEvent->respondent->full_name
                )
            );
        }
    }
}
