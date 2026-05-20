<?php

namespace App\Console;

use App\Jobs\CheckSlaBreaches;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Check SLA breaches every 15 minutes
        $schedule->job(new CheckSlaBreaches)
                 ->everyFifteenMinutes()
                 ->withoutOverlapping()
                 ->onOneServer()
                 ->name('sla-check');

        // Prune old notification records weekly
        $schedule->command('notifications:prune --days=90')
                 ->weekly()
                 ->sundays()
                 ->at('02:00');
    }

    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');
        require base_path('routes/console.php');
    }
}