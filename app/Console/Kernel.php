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
        $schedule->command('grades:promote')->yearlyOn(9, 1, '06:00');

        // Telegram verifikatsiya eskirgan yozuvlarini tozalash
        $schedule->command('telegram:clean-expired')->hourly();

        // Telegram getUpdates polling (webhook o'rniga, local uchun)
        $schedule->command('telegram:poll --once')->everyMinute()->withoutOverlapping();

        if (filter_var(env('BACKUP_SCHEDULE_ENABLED', false), FILTER_VALIDATE_BOOLEAN)) {
            $schedule->command('backup:clean')->daily()->at('01:00');
            $schedule->command('backup:run')->daily()->at('01:30');
        }
    }

    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');

        require base_path('routes/console.php');
    }
}
