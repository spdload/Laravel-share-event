<?php

namespace App\Transitions\ShareEvent;

use App\Models\ShareEvent;
use App\States\ShareEvent\Connected;
use Spatie\ModelStates\Transition;

class InitialToConnected extends Transition
{
    private ShareEvent $shareEvent;

    public function __construct(ShareEvent $shareEvent)
    {
        $this->shareEvent = $shareEvent;
    }

    public function handle(): ShareEvent
    {
        $this->shareEvent->state = new Connected($this->shareEvent);
        $this->shareEvent->save();

        return $this->shareEvent;
    }
}
