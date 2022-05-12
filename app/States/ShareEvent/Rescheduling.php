<?php

namespace App\States\ShareEvent;

use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;

class Rescheduling extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::RESCHEDULING;

    public function getClientState(int $userId): string
    {
        return ShareEventClientStatesEnum::RESCHEDULING;
    }
}
