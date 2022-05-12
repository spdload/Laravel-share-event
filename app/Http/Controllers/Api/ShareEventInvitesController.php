<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateShareEventInviteRequest;
use App\Http\Resources\ShareEventResource;
use App\Models\ShareEvent;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareEventInvitesController extends Controller
{
    /**
     * @throws \Spatie\ModelStates\Exceptions\InvalidConfig
     */
    public function store(CreateShareEventInviteRequest $request, ShareEvent $shareEvent): JsonResource
    {
        $shareEvent->update($request->validated());
        $shareEvent->invite($request->user());

        return new ShareEventResource($shareEvent);
    }
}
