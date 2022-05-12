<?php

namespace App\States\ShareEvent;

use App\Enums\ShareEventClientStatesEnum;
use App\Enums\ShareEventStatesEnum;

class Confirmed extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::CONFIRMED;

    public function getClientState(int $userId): string
    {
        return ShareEventClientStatesEnum::COMING_RIGHT_UP;
    }
}
