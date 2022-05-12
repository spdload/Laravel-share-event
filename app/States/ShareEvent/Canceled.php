<?php

namespace App\States\ShareEvent;

use App\Enums\ShareEventStatesEnum;

class Canceled extends ShareEventState
{
    public static string $name = ShareEventStatesEnum::CANCELED;

    public function getClientState(int $userId): string
    {
        return ShareEventStatesEnum::CANCELED;
    }
}
