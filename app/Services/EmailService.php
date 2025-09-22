<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use App\Mail\AppointmentBookedMail;
use App\Mail\AppointmentStatusChangedMail;
use App\Mail\PaymentStatusMail;
use App\Mail\VerifyRegistrationMail;

class EmailService
{
    /**
     * Send appointment booked email
     */
    public function sendAppointmentBooked(array $data): bool
    {
        try {
            // Use SmartEmailService to handle Resend limitations
            $smartEmailService = new \App\Services\SmartEmailService();
            $result = $smartEmailService->sendAppointmentBooked($data);
            
            if ($result) {
                Log::info('Appointment booked email sent successfully', [
                    'email' => $data['email'],
                    'appointment_id' => $data['appointment_id'] ?? null
                ]);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::error('Failed to send appointment booked email', [
                'email' => $data['email'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send appointment status changed email
     */
    public function sendAppointmentStatusChanged(array $data): bool
    {
        try {
            // Use SmartEmailService to handle Resend limitations
            $smartEmailService = new \App\Services\SmartEmailService();
            $result = $smartEmailService->sendAppointmentStatusChanged($data);
            
            if ($result) {
                Log::info('Appointment status changed email sent successfully', [
                    'email' => $data['email'],
                    'appointment_id' => $data['appointment_id'] ?? null,
                    'status' => $data['status'] ?? null
                ]);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::error('Failed to send appointment status changed email', [
                'email' => $data['email'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send payment status email
     */
    public function sendPaymentStatus(array $data): bool
    {
        try {
            // Use SmartEmailService to handle Resend limitations
            $smartEmailService = new \App\Services\SmartEmailService();
            $result = $smartEmailService->sendPaymentStatus($data);
            
            if ($result) {
                Log::info('Payment status email sent successfully', [
                    'email' => $data['email'],
                    'appointment_id' => $data['appointment_id'] ?? null,
                    'status' => $data['status'] ?? null
                ]);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::error('Failed to send payment status email', [
                'email' => $data['email'],
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send registration verification email
     */
    public function sendRegistrationVerification(string $email, string $name, string $verifyUrl): bool
    {
        try {
            // Use SmartEmailService to handle Resend limitations
            $smartEmailService = new \App\Services\SmartEmailService();
            $result = $smartEmailService->sendRegistrationVerification($email, $name, $verifyUrl);
            
            if ($result) {
                Log::info('Registration verification email sent successfully', [
                    'email' => $email
                ]);
            }
            return $result;
        } catch (\Throwable $e) {
            Log::error('Failed to send registration verification email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }


    /**
     * Send custom email with raw content
     */
    public function sendRawEmail(string $email, string $subject, string $content): bool
    {
        try {
            Mail::raw($content, function ($message) use ($email, $subject) {
                $message->to($email)->subject($subject);
            });
            Log::info('Raw email sent successfully', [
                'email' => $email,
                'subject' => $subject
            ]);
            return true;
        } catch (\Throwable $e) {
            Log::error('Failed to send raw email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Check if email service is properly configured
     */
    public function isConfigured(): bool
    {
        $mailer = config('mail.default');
        $resendKey = config('mail.mailers.resend.key');
        
        if ($mailer === 'resend' && !empty($resendKey)) {
            return true;
        }
        
        return false;
    }

    /**
     * Get email configuration status
     */
    public function getConfigurationStatus(): array
    {
        return [
            'mailer' => config('mail.default'),
            'resend_configured' => !empty(config('mail.mailers.resend.key')),
            'from_address' => config('mail.from.address'),
            'from_name' => config('mail.from.name'),
        ];
    }
}
