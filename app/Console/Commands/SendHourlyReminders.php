<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Services\SmsService;
use App\Models\Notification;
use Carbon\Carbon;

class SendHourlyReminders extends Command
{
    protected $signature = 'appointments:send-hourly-reminders';
    protected $description = 'Send SMS and notification reminders for appointments 1 hour before';

    public function handle()
    {
        $this->info('Starting 1-hour appointment reminder process...');
        
        // Get appointments that are 1 hour away
        $reminderTime = Carbon::now()->addHour();
        $appointments = Appointment::where('status', 'approved')
            ->whereDate('appointment_date', $reminderTime->format('Y-m-d'))
            ->whereTime('appointment_time', $reminderTime->format('H:i'))
            ->where('hourly_reminder_sent', false)
            ->with(['shop', 'service'])
            ->get();

        $this->info("Found {$appointments->count()} appointments needing 1-hour reminders.");

        foreach ($appointments as $appointment) {
            $this->sendHourlyReminder($appointment);
        }

        $this->info('1-hour appointment reminder process completed.');
    }

    private function sendHourlyReminder(Appointment $appointment)
    {
        try {
            // Send SMS reminder
            $sms = app(SmsService::class);
            $to = $sms->toE164($appointment->phone);
            
            if ($to) {
                $message = "Hi {$appointment->customer_name}! 📅 Friendly reminder: Your auto repair appointment is in 1 hour at {$appointment->appointment_time->format('h:i A')}. Please prepare to arrive on time. See you soon! 🚗";
                $sms->send($to, $message);
                $this->info("1-hour SMS reminder sent to {$appointment->customer_name} ({$appointment->phone})");
            }

            // Create in-app notification for customer
            Notification::create([
                'user_id' => $appointment->user_id,
                'type' => 'appointment_reminder',
                'title' => 'Appointment Reminder - 1 Hour',
                'message' => "Your appointment is in 1 hour at {$appointment->appointment_time->format('h:i A')}",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'reminder_type' => '1_hour',
                    'appointment_time' => $appointment->appointment_time->format('H:i'),
                    'shop_name' => $appointment->shop->name ?? 'Auto Repair Shop'
                ],
            ]);

            // Notify shop owner about upcoming appointment
            if ($appointment->shop && $appointment->shop->admin_id) {
                Notification::create([
                    'admin_id' => $appointment->shop->admin_id,
                    'shop_id' => $appointment->shop->id,
                    'type' => 'appointment_reminder_admin',
                    'title' => 'Customer Appointment in 1 Hour',
                    'message' => "Customer {$appointment->customer_name} has an appointment in 1 hour at {$appointment->appointment_time->format('h:i A')}",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'customer_name' => $appointment->customer_name,
                        'customer_phone' => $appointment->phone,
                        'reminder_type' => '1_hour',
                        'appointment_time' => $appointment->appointment_time->format('H:i'),
                        'service_name' => $appointment->service->name ?? 'Unknown Service'
                    ],
                ]);
            }

            // Mark hourly reminder as sent
            $appointment->update(['hourly_reminder_sent' => true]);

        } catch (\Throwable $e) {
            $this->error("Failed to send 1-hour reminder for appointment #{$appointment->id}: " . $e->getMessage());
        }
    }
}
