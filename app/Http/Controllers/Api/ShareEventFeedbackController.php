<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateShareEventFeedbackRequest;
use App\Http\Resources\ShareEventFeedbackResource;
use App\Models\ShareEvent;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareEventFeedbackController extends Controller
{
    public function store(CreateShareEventFeedbackRequest $request, ShareEvent $shareEvent): JsonResource
    {
        return new ShareEventFeedbackResource($shareEvent->createFeedback(
            $request->user(),
            $request->get('type'),
            $request->get('additional_information')
        ));
    }
}
