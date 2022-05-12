<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateShareReviewRequest;
use App\Http\Requests\UpdateShareReviewRequest;
use App\Http\Resources\ShareEventResource;
use App\Models\ShareEvent;
use App\Models\ShareReview;
use App\States\ShareEvent\ReviewWaiting;

class ShareReviewsController extends Controller
{
    public function store(CreateShareReviewRequest $request, ShareEvent $shareEvent)
    {
        $shareEvent->reviews()->save(ShareReview::make([
            'text' => $request['text'],
        ])->user()->associate($request->user()))
            ->tap(function (ShareReview $shareReview) use ($request) {
                $shareReview->sendForPartner(request()->user()->id);
                if ($request->hasFile('image')) {
                    $shareReview->addMediaFromRequest('image')->toMediaCollection();
                }
            });

        $shareEvent->changeByPartner($request->user()->id);

        return new ShareEventResource($shareEvent->fresh());
    }

    public function update(UpdateShareReviewRequest $request, ShareEvent $shareEvent, ShareReview $shareReview)
    {
        $shareReview->update([
            'is_read' => $request->boolean('is_read'),
        ]);

        if ($shareEvent->reviews->where('is_read', true)->count() === 2
            && $shareEvent->state->is(ReviewWaiting::class)) {
            $shareEvent->transitionToCompleted();
        }

        $shareEvent->changeByPartner($request->user()->id);

        return new ShareEventResource($shareEvent);
    }
}
