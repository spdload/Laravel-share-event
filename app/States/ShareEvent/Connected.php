<?php

namespace App\States\ShareEvent;

use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;

class Connected extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::CONNECTED;

    public function getClientState(int $userId): string
    {
        return ShareEventClientStatesEnum::CONNECTED;
    }
}
