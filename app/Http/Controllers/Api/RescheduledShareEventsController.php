<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Resources\ShareEventResource;
use App\Models\ShareEvent;
use App\States\ShareEvent\Rescheduling;
use Illuminate\Http\Request;

class RescheduledShareEventsController extends Controller
{
    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     */
    public function store(Request $request, ShareEvent $shareEvent)
    {
        $this->authorize('access', $shareEvent);

        if ($shareEvent->state->is(Rescheduling::class)) {
            laraflash(__('Your partner has already rescheduled current event'))->success()->now();

            return new ShareEventResource($shareEvent);
        }

        laraflash(__('Your share event has been rescheduled.'))->success()->now();

        $shareEvent->reschedule($request->user());
        $shareEvent->changeByPartner($request->user()->id);

        return new ShareEventResource($shareEvent);
    }
}
