<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlantActivityTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShareEventResource;
use App\Jobs\DeleteShareEventJob;
use App\Models\ShareEvent;
use App\Notifications\CancelShareEventNotification;
use App\Notifications\InvitationDeclinedNotification;
use App\Notifications\RequestDeclinedNotification;
use App\States\ShareEvent\Canceled;
use App\States\ShareEvent\Initial;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class CancelledShareEventsController extends Controller
{
    /**
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, ShareEvent $shareEvent)
    {
        $this->authorize('access', $shareEvent);
        $sharePartner = $shareEvent->getSharePartner($request->user()->id);

        if ($shareEvent->state->is(Initial::class)) {
            $shareEvent->getActiveType($request->user()->id) === PlantActivityTypesEnum::LOVE
                ? $sharePartner->notify(
                    new InvitationDeclinedNotification(
                        $shareEvent,
                        $request->user(),
                        $sharePartner->full_name
                    )
                )
                : $sharePartner->notify(
                    new RequestDeclinedNotification(
                        $shareEvent,
                        $request->user(),
                        $sharePartner->full_name
                    )
                );

            $shareEvent->state->transitionTo(Canceled::class);

            dispatch(new DeleteShareEventJob($shareEvent))->delay(now()->addMinutes(15));

            return response()->noContent(Response::HTTP_OK);
        }

        if ($shareEvent->state->is(Canceled::class)) {
            laraflash(__('Your partner has already Canceled current event'))->success()->now();

            return new ShareEventResource($shareEvent);
        }

        $sharePartner->notify(new CancelShareEventNotification($shareEvent, $request->user(), $sharePartner->full_name));
        $shareEvent->state->transitionTo(Canceled::class);

        return new ShareEventResource($shareEvent);
    }
}
