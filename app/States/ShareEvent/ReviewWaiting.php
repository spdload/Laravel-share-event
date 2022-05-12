<?php

namespace App\States\ShareEvent;

use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;

class ReviewWaiting extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::REVIEW_WAITING;

    public function getClientState(int $userId): string
    {
        if (! $this->model->isReviewMissing($userId, true)) {
            return ShareEventClientStatesEnum::REVIEW_RECEIVED;
        }

        if (! $this->model->isReviewMissing($userId) && $this->model->isReviewMissing($userId, true)) {
            return ShareEventClientStatesEnum::REVIEW_SENT;
        }

        return ShareEventClientStatesEnum::REVIEW_WAITING;
    }
}
