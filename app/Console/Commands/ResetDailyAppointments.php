<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use Carbon\Carbon;

class ResetDailyAppointments extends Command
{
    protected $signature = 'appointments:reset-daily';
    protected $description = 'Reset daily appointment counts at midnight';

    public function handle()
    {
        $today = \Carbon\Carbon::today();
        $tomorrow = $today->copy()->addDay();

        // 1. Delete all of today's appointments
        \App\Models\Appointment::whereDate('appointment_date', $today)->delete();

        // 2. Move all tomorrow's appointments to today
        \App\Models\Appointment::whereDate('appointment_date', $tomorrow)
            ->update(['appointment_date' => $today->format('Y-m-d')]);

        $this->info('Today\'s appointments cleared and tomorrow\'s credited to today.');
    }
} 