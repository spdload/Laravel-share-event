<?php

namespace App\States\ShareEvent;

use App\Enums\PlantActivityTypesEnum;
use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;

class Initial extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::INITIAL;

    public function getClientState(int $userId): string
    {
        if ($this->model->initiator_id === $userId && $this->model->type === PlantActivityTypesEnum::LOVE) {
            return ShareEventClientStatesEnum::REQUEST_SENT;
        } elseif ($this->model->initiator_id === $userId && $this->model->type === PlantActivityTypesEnum::SHARE) {
            return ShareEventClientStatesEnum::INVITATION_SENT;
        } elseif ($this->model->respondent_id === $userId && $this->model->type === PlantActivityTypesEnum::LOVE) {
            return ShareEventClientStatesEnum::NEW_REQUEST;
        }

        return ShareEventClientStatesEnum::NEW_INVITATION;
    }
}
