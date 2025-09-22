<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Appointment;
use App\Services\SmsService;
use App\Models\Notification;
use Carbon\Carbon;

class SendAppointmentReminders extends Command
{
    protected $signature = 'appointments:send-reminders';
    protected $description = 'Send SMS and notification reminders for upcoming appointments';

    public function handle()
    {
        $this->info('Starting appointment reminder process...');
        
        // Get appointments that are 5 minutes away
        $reminderTime = Carbon::now()->addMinutes(5);
        $appointments = Appointment::where('status', 'approved')
            ->whereDate('appointment_date', $reminderTime->format('Y-m-d'))
            ->whereTime('appointment_time', $reminderTime->format('H:i'))
            ->where('reminder_sent', false)
            ->with(['shop', 'service'])
            ->get();

        $this->info("Found {$appointments->count()} appointments needing reminders.");

        foreach ($appointments as $appointment) {
            $this->sendReminder($appointment);
        }

        $this->info('Appointment reminder process completed.');
    }

    private function sendReminder(Appointment $appointment)
    {
        try {
            // Send SMS reminder
            $sms = app(SmsService::class);
            $to = $sms->toE164($appointment->phone);
            
            if ($to) {
                $message = "Hi {$appointment->customer_name}! ⏰ Reminder: Your appointment is in 5 minutes at {$appointment->appointment_time->format('h:i A')}. Please arrive on time. Thank you! 🚗";
                $sms->send($to, $message);
                $this->info("SMS reminder sent to {$appointment->customer_name} ({$appointment->phone})");
            }

            // Create in-app notification for customer
            Notification::create([
                'user_id' => $appointment->user_id,
                'type' => 'appointment_reminder',
                'title' => 'Appointment Reminder',
                'message' => "Your appointment is in 5 minutes at {$appointment->appointment_time->format('h:i A')}",
                'data' => [
                    'appointment_id' => $appointment->id,
                    'reminder_type' => '5_minutes',
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
                    'title' => 'Customer Arriving Soon',
                    'message' => "Customer {$appointment->customer_name} has an appointment in 5 minutes at {$appointment->appointment_time->format('h:i A')}",
                    'data' => [
                        'appointment_id' => $appointment->id,
                        'customer_name' => $appointment->customer_name,
                        'customer_phone' => $appointment->phone,
                        'reminder_type' => '5_minutes',
                        'appointment_time' => $appointment->appointment_time->format('H:i'),
                        'service_name' => $appointment->service->name ?? 'Unknown Service'
                    ],
                ]);
            }

            // Mark reminder as sent
            $appointment->update(['reminder_sent' => true]);

        } catch (\Throwable $e) {
            $this->error("Failed to send reminder for appointment #{$appointment->id}: " . $e->getMessage());
        }
    }
}
