<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;

class ResendMailService
{
    protected $apiKey;
    protected $fromEmail;
    protected $fromName;

    public function __construct()
    {
        $this->apiKey = config('services.resend.key');
        $this->fromEmail = config('mail.from.address', 'onboarding@resend.dev');
        $this->fromName = config('mail.from.name', 'Auto Repair Shop');
    }

    /**
     * Send email using Resend API directly
     */
    public function sendEmail(string $to, string $subject, string $htmlContent, string $textContent = null): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => $this->fromName . ' <' . $this->fromEmail . '>',
                'to' => [$to],
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent,
            ]);

            if ($response->successful()) {
                Log::info('Email sent successfully via Resend API', [
                    'to' => $to,
                    'subject' => $subject,
                    'response' => $response->json()
                ]);
                return true;
            } else {
                Log::error('Failed to send email via Resend API', [
                    'to' => $to,
                    'subject' => $subject,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception while sending email via Resend API', [
                'to' => $to,
                'subject' => $subject,
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
        $subject = 'Verify your email';
        
        $htmlContent = view('emails.verify-registration', [
            'name' => $name,
            'verifyUrl' => $verifyUrl
        ])->render();

        $textContent = "Hi {$name},\n\n";
        $textContent .= "Thanks for signing up to Auto Repair Shop. Please confirm your email to finish creating your account.\n\n";
        $textContent .= "Click this link to verify: {$verifyUrl}\n\n";
        $textContent .= "If you didn't request this, you can ignore this email.";

        return $this->sendEmail($email, $subject, $htmlContent, $textContent);
    }

    /**
     * Send appointment booked email
     */
    public function sendAppointmentBooked(array $data): bool
    {
        $subject = 'Appointment Booked - ' . ($data['service_name'] ?? 'Auto Repair Service');
        
        $htmlContent = view('emails.appointment-booked', $data)->render();

        $textContent = "Hi {$data['customer_name']},\n\n";
        $textContent .= "Your appointment has been booked successfully.\n\n";
        $textContent .= "Appointment Details:\n";
        $textContent .= "Date: {$data['appointment_date']}\n";
        $textContent .= "Time: {$data['appointment_time']}\n";
        $textContent .= "Service: " . ($data['service_name'] ?? 'Auto Repair Service') . "\n\n";
        $textContent .= "Thank you for choosing Auto Repair Shop!";

        return $this->sendEmail($data['email'], $subject, $htmlContent, $textContent);
    }

    /**
     * Send appointment status changed email
     */
    public function sendAppointmentStatusChanged(array $data): bool
    {
        $subject = 'Appointment Status Update - ' . ($data['service_name'] ?? 'Auto Repair Service');
        
        $htmlContent = view('emails.appointment-status-changed', $data)->render();

        $textContent = "Hi {$data['customer_name']},\n\n";
        $textContent .= "Your appointment status has been updated to: {$data['status']}\n\n";
        $textContent .= "Appointment Details:\n";
        $textContent .= "Date: {$data['appointment_date']}\n";
        $textContent .= "Time: {$data['appointment_time']}\n";
        $textContent .= "Service: " . ($data['service_name'] ?? 'Auto Repair Service') . "\n\n";
        $textContent .= "Thank you for choosing Auto Repair Shop!";

        return $this->sendEmail($data['email'], $subject, $htmlContent, $textContent);
    }

    /**
     * Send payment status email
     */
    public function sendPaymentStatus(array $data): bool
    {
        $subject = 'Payment Status Update - ' . ($data['service_name'] ?? 'Auto Repair Service');
        
        $htmlContent = view('emails.payment-status', $data)->render();

        $textContent = "Hi {$data['customer_name']},\n\n";
        $textContent .= "Your payment status has been updated to: {$data['status']}\n\n";
        $textContent .= "Payment Details:\n";
        $textContent .= "Amount: $" . ($data['amount'] ?? '0.00') . "\n";
        $textContent .= "Method: " . ($data['payment_method'] ?? 'N/A') . "\n\n";
        $textContent .= "Thank you for choosing Auto Repair Shop!";

        return $this->sendEmail($data['email'], $subject, $htmlContent, $textContent);
    }
}
