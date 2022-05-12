<?php

namespace App\Http\Controllers\Api;

use App\DTO\ShareMessageData;
use App\Events\MessageSent;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\CreateShareMessageRequest;
use App\Http\Resources\ShareMessageResource;
use App\Models\ShareEvent;
use App\Models\ShareMessage;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class ShareEventMessagesController extends Controller
{
    private const PER_PAGE = 10;

    /**
     * @throws \Illuminate\Auth\Access\AuthorizationException
     */
    public function index(Request $request, ShareEvent $shareEvent): JsonResource
    {
        $this->authorize('access', $shareEvent);

        $shareEvent->messages()->where('user_id', '<>', $request->user()->id)
            ->update(['is_read' => true]);

        return ShareMessageResource::collection(
            $shareEvent->messages()
                ->with(['media', 'user:id,first_name'])
                ->latest()
                ->paginate(self::PER_PAGE)
        );
    }

    public function store(CreateShareMessageRequest $request, ShareEvent $shareEvent)
    {
        $shareMessage = ShareMessage::createForShareEvent($shareEvent, ShareMessageData::fromRequest($request));

        broadcast(new MessageSent(
            $request->user(),
            $shareMessage,
            $shareEvent->id
        ))->toOthers();

        return new ShareMessageResource($shareMessage);
    }
}
