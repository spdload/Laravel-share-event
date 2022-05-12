<?php

namespace App\Http\Controllers\Api;

use App\Enums\PlantActivityTypesEnum;
use App\Http\Controllers\Controller;
use App\Http\Resources\ShareEventResource;
use App\Models\ShareEvent;
use App\Notifications\InvitationAcceptedNotification;
use App\Notifications\RequestAcceptedNotification;
use App\States\ShareEvent\Connected;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class AcceptedShareEventsController extends Controller
{
    /**
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function store(Request $request, ShareEvent $shareEvent): JsonResource
    {
        $this->authorize('access', $shareEvent);

        $shareEvent->state->transitionTo(Connected::class);

        $shareEvent->getActiveType($request->user()->id) === PlantActivityTypesEnum::LOVE
            ? $shareEvent->initiator->notify(new InvitationAcceptedNotification(
                $shareEvent,
                $shareEvent->respondent,
                $shareEvent->initiator->full_name
            ))
            : $shareEvent->initiator->notify(new RequestAcceptedNotification(
                $shareEvent,
                $shareEvent->respondent,
                $shareEvent->initiator->full_name
            ));

        $shareEvent->changeByPartner($request->user()->id);

        return new ShareEventResource($shareEvent);
    }
}
