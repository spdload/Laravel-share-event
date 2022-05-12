<?php

namespace App\States\ShareEvent;

use App\Enums\PlantActivityTypesEnum;
use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;

class ConfirmationWaiting extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::CONFIRMATION_WAITING;

    public function getClientState(int $userId): string
    {
        if ($this->model->getActiveType($userId) === PlantActivityTypesEnum::SHARE) {
            return ShareEventClientStatesEnum::CONFIRMATION_WAITING;
        }

        return ShareEventClientStatesEnum::REPLY_WAITING;
    }
}
