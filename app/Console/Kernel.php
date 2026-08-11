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

        // Muddati tugagan bloklangan foydalanuvchilarni avtomatik ochish — har 5 daqiqada
        $schedule->command('users:unblock-expired')->everyFiveMinutes();

        // Muddati tugagan donorlarni oddiy foydalanuvchiga tushirish — har daqiqada
        $schedule->command('donations:expire')->everyMinute();

        // Donor muddati tugashidan oldin ogohlantirish — kuniga 1 marta (ertalab 9:00)
        $schedule->command('donors:warn-expiring')->dailyAt('09:00');

        // Telegram getUpdates polling — webhook faol bo'lsa ishlamaydi
        // Agar webhook o'chirilgan bo'lsa, quyidagini yoqing:
        // if (app()->environment('local')) {
        //     $schedule->command('telegram:poll --once')->everyMinute()->withoutOverlapping();
        // }

        // Kurs boshlanishi haqida eslatma — kuniga 1 marta (ertalab 8:00)
        $schedule->command('telegram:course-reminders')->dailyAt('08:00');

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
