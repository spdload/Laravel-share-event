<?php

namespace App\Jobs;

use App\Models\ShareEvent;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class DeleteShareEventJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected ShareEvent $shareEvent;

    public function __construct(ShareEvent $shareEvent)
    {
        $this->shareEvent = $shareEvent;
    }

    public function handle()
    {
        $this->shareEvent->delete();
    }
}
