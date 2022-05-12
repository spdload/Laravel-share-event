<?php

namespace App\Console;

use App\Console\Commands\MigrateClimateZoneFromVarietyToSpeciesCommand;
use App\Jobs\CarouselPlantChange;
use App\Jobs\CheckMissedMessagesJob;
use App\Jobs\TransitShareEventState;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * The Artisan commands provided by your application.
     *
     * @var array
     */
    protected $commands = [
        MigrateClimateZoneFromVarietyToSpeciesCommand::class,
    ];

    /**
     * Define the application's command schedule.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        $schedule->command('horizon:snapshot')->everyFiveMinutes();

        $schedule->command('backup:clean')->daily()->at('01:00');
        $schedule->command('backup:run')->daily()->at('02:00');
        $schedule->command('backup:monitor')->daily()->at('03:00');

        $schedule->command('telescope:prune')->daily();

        $schedule->job(TransitShareEventState::class)->everyMinute();
        $schedule->job(CheckMissedMessagesJob::class)->hourly();
    }

    /**
     * Register the commands for the application.
     *
     * @return void
     */
    protected function commands()
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
