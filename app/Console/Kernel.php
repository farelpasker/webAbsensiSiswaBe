<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    protected function schedule(Schedule $schedule): void
    {
        // Generate attendance records untuk semua siswa setiap hari jam 06:00
        $schedule->command('attendance:generate')
            ->dailyAt('06:00')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/attendance-generate.log'));

        // Jalankan reminder setiap hari jam 07:55 (5 menit sebelum telat jam 08:00)
        $schedule->command('app:send-attendance-reminder')
            ->dailyAt('07:55')
            ->withoutOverlapping()
            ->appendOutputTo(storage_path('logs/attendance-reminder.log'));
    }

    protected function commands(): void
    {
        $this->load(__DIR__.'/Commands');
        require base_path('routes/console.php');
    }
}
