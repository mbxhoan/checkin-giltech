<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $schedule->command('backup:clean')->dailyAt('01:00');
        $schedule->command('backup:run')->dailyAt('02:00');
        $schedule->command('telescope:prune')->dailyAt('03:00');
        $schedule->command('videc:cleanup-registration-files --hours=24')
            ->hourly()
            ->withoutOverlapping();

        if (config('onepay.querydr_retry_enabled')) {
            $schedule->command('videc:querydr --pending --limit=25')
                ->everyFiveMinutes()
                ->withoutOverlapping();
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
