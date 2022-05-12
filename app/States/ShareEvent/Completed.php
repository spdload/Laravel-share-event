<?php

namespace App\States\ShareEvent;

use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;

class Completed extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::COMPLETED;

    public function getClientState(int $userId): string
    {
        return ShareEventClientStatesEnum::COMPLETED;
    }
}
