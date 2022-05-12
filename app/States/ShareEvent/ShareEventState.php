<?php

namespace App\States\ShareEvent;

use Spatie\ModelStates\State;

abstract class ShareEventState extends State
{
    abstract public function getClientState(int $userId): string;
}
