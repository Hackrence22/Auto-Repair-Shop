<?php

namespace App\Console;

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     *
     * These cron jobs are run in the background by a cron service.
     *
     * @param  \Illuminate\Console\Scheduling\Schedule  $schedule
     * @return void
     */
    protected function schedule(Schedule $schedule)
    {
        // Reset daily appointments at midnight
        $schedule->command('appointments:reset-daily')
                ->dailyAt('00:00')
                ->withoutOverlapping();

        // Send 1-hour appointment reminders every minute
        $schedule->command('appointments:send-hourly-reminders')
                ->everyMinute()
                ->withoutOverlapping();

        // Send 5-minute appointment reminders every minute
        $schedule->command('appointments:send-reminders')
                ->everyMinute()
                ->withoutOverlapping();
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