<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShareEventResource;
use App\Jobs\SentReminderJob;
use App\Models\ShareEvent;
use App\Notifications\InviteConfirmedNotification;
use App\States\ShareEvent\Confirmed;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ConfirmedShareEventsController extends Controller
{
    /**
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, ShareEvent $shareEvent): JsonResource
    {
        $this->authorize('access', $shareEvent);

        $sharePartner = $shareEvent->getSharePartner($request->user()->id);

        $sharePartner->notify(
            new InviteConfirmedNotification(
                $shareEvent,
                $request->user(),
                $sharePartner->full_name
            )
        );

        if (Carbon::make($shareEvent->share_date)->diffInHours(now()) >= 2) {
            dispatch(new SentReminderJob($shareEvent))
                ->delay($shareEvent->share_date->subHours(2));
        }

        if (Carbon::make($shareEvent->share_date)->diffInHours(now()) >= 24) {
            dispatch(new SentReminderJob($shareEvent))
                ->delay($shareEvent->share_date->subDay());
        }

        laraflash(__('Your share event has been confirmed.'))->success()->now();

        $shareEvent->changeByPartner($request->user()->id);

        return new ShareEventResource(
            $shareEvent->state->transitionTo(Confirmed::class)
        );
    }
}
