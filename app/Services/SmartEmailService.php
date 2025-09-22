<?php

namespace App\Services;

use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use App\Mail\VerifyRegistrationMail;

class SmartEmailService
{
    protected $apiKey;
    protected $fromEmail;
    protected $fromName;
    protected $verifiedEmail;

    public function __construct()
    {
        $this->apiKey = config('services.resend.key');
        $this->fromEmail = config('mail.from.address', 'onboarding@resend.dev');
        $this->fromName = config('mail.from.name', 'Auto Repair Shop');
        $this->verifiedEmail = 'clarencelisondra45@gmail.com'; // Your verified email
    }

    /**
     * Send registration verification email with smart handling
     */
    public function sendRegistrationVerification(string $email, string $name, string $verifyUrl): bool
    {
        try {
            // If it's the verified email, send directly
            if ($email === $this->verifiedEmail) {
                return $this->sendDirectEmail($email, $name, $verifyUrl);
            }
            
            // For other emails, send notification to verified email
            return $this->sendVerificationNotification($email, $name, $verifyUrl);
            
        } catch (\Exception $e) {
            Log::error('Failed to send registration verification email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send email directly to verified address
     */
    private function sendDirectEmail(string $email, string $name, string $verifyUrl): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => $this->fromName . ' <' . $this->fromEmail . '>',
                'to' => [$email],
                'subject' => 'Verify your email - Auto Repair Shop',
                'html' => view('emails.verify-registration', [
                    'name' => $name,
                    'verifyUrl' => $verifyUrl
                ])->render(),
            ]);

            if ($response->successful()) {
                Log::info('Verification email sent directly', ['email' => $email]);
                return true;
            } else {
                Log::error('Failed to send direct verification email', [
                    'email' => $email,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception sending direct verification email', [
                'email' => $email,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send verification email to user with admin notification
     */
    private function sendVerificationNotification(string $userEmail, string $userName, string $verifyUrl): bool
    {
        try {
            // Send verification email to the user (this will fail due to Resend limitation, but we try anyway)
            $userEmailSent = $this->sendDirectEmail($userEmail, $userName, $verifyUrl);
            
            // Always send admin notification for manual verification
            $adminNotificationSent = $this->sendAdminVerificationNotification($userEmail, $userName, $verifyUrl);
            
            // Return true if either email was sent successfully
            return $userEmailSent || $adminNotificationSent;
            
        } catch (\Exception $e) {
            Log::error('Exception sending verification notification', [
                'user_email' => $userEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send admin notification for manual verification
     */
    private function sendAdminVerificationNotification(string $userEmail, string $userName, string $verifyUrl): bool
    {
        try {
            $subject = "New User Registration - Manual Verification Required";
            
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #0d6efd;'>New User Registration - Manual Verification Required</h2>
                    <p>A new user has registered and needs manual email verification:</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3>User Details:</h3>
                        <p><strong>Name:</strong> {$userName}</p>
                        <p><strong>Email:</strong> {$userEmail}</p>
                        <p><strong>Registration Time:</strong> " . now()->format('Y-m-d H:i:s') . "</p>
                    </div>
                    
                    <div style='background: #e3f2fd; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3>Verification Link:</h3>
                        <p>Click the button below to verify this user's email:</p>
                        <p style='margin: 20px 0;'>
                            <a href='{$verifyUrl}' style='background: #0d6efd; color: #fff; padding: 12px 24px; border-radius: 8px; text-decoration: none; display: inline-block;'>
                                Verify User Account
                            </a>
                        </p>
                        <p style='font-size: 12px; color: #666;'>
                            Or copy this link: <br>
                            <span style='word-break: break-all; color: #0d6efd;'>{$verifyUrl}</span>
                        </p>
                    </div>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4>Instructions:</h4>
                        <ol>
                            <li>Click the verification link above</li>
                            <li>This will activate the user's account</li>
                            <li>The user will be able to log in normally</li>
                            <li>You can also forward this email to the user if needed</li>
                        </ol>
                    </div>
                    
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;' />
                    <p style='font-size: 12px; color: #6c757d;'>
                        This is an automated notification from Auto Repair Shop System.
                    </p>
                </div>
            ";

            $textContent = "New User Registration - Manual Verification Required\n\n";
            $textContent .= "User: {$userName} ({$userEmail})\n";
            $textContent .= "Registration Time: " . now()->format('Y-m-d H:i:s') . "\n\n";
            $textContent .= "Verification Link: {$verifyUrl}\n\n";
            $textContent .= "Click the link above to verify this user's email address.\n";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => $this->fromName . ' <' . $this->fromEmail . '>',
                'to' => [$this->verifiedEmail],
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent,
            ]);

            if ($response->successful()) {
                Log::info('Admin verification notification sent', [
                    'user_email' => $userEmail,
                    'user_name' => $userName
                ]);
                return true;
            } else {
                Log::error('Failed to send admin verification notification', [
                    'user_email' => $userEmail,
                    'status' => $response->status(),
                    'response' => $response->body()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::error('Exception sending admin verification notification', [
                'user_email' => $userEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send appointment booked email
     */
    public function sendAppointmentBooked(array $data): bool
    {
        try {
            $userEmail = $data['email'] ?? null;
            $customerName = $data['customer_name'] ?? 'Customer';
            
            if (!$userEmail) {
                Log::error('No email provided for appointment booking notification');
                return false;
            }

            // Prepare data for email template
            $emailData = [
                'user_name' => $data['customer_name'] ?? 'Customer',
                'date' => $data['appointment_date'] ?? 'Unknown',
                'time' => $data['appointment_time'] ?? 'Unknown',
                'service' => $data['service_name'] ?? 'Auto Repair Service',
                'shop' => $data['shop'] ?? null,
            ];

            // Try to send to user first
            $userEmailSent = $this->sendEmailToUser($userEmail, 
                'Appointment Booked - ' . ($data['service_name'] ?? 'Auto Repair Service'),
                view('emails.appointments.booked', $emailData)->render(),
                $this->getAppointmentBookedTextContent($data)
            );

            // Always send admin notification as well
            $adminNotificationSent = $this->sendAdminAppointmentNotification($data, 'booked');

            return $userEmailSent || $adminNotificationSent;
        } catch (\Exception $e) {
            Log::error('Failed to send appointment booked email', [
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
            $userEmail = $data['email'] ?? null;
            
            if (!$userEmail) {
                Log::error('No email provided for appointment status change notification');
                return false;
            }

            // Prepare data for email template
            $emailData = [
                'user_name' => $data['customer_name'] ?? 'Customer',
                'date' => $data['appointment_date'] ?? 'Unknown',
                'time' => $data['appointment_time'] ?? 'Unknown',
                'service' => $data['service_name'] ?? 'Auto Repair Service',
                'status' => $data['status'] ?? 'Unknown',
                'shop' => $data['shop'] ?? null,
            ];

            // Try to send to user first
            $userEmailSent = $this->sendEmailToUser($userEmail, 
                'Appointment Status Update - ' . ($data['service_name'] ?? 'Auto Repair Service'),
                view('emails.appointments.status-changed', $emailData)->render(),
                $this->getAppointmentStatusChangedTextContent($data)
            );

            // Always send admin notification as well
            $adminNotificationSent = $this->sendAdminAppointmentNotification($data, 'status_changed');

            return $userEmailSent || $adminNotificationSent;
        } catch (\Exception $e) {
            Log::error('Failed to send appointment status changed email', [
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
            $userEmail = $data['email'] ?? null;
            
            if (!$userEmail) {
                Log::error('No email provided for payment status notification');
                return false;
            }

            // Prepare data for email template
            $emailData = [
                'user_name' => $data['customer_name'] ?? 'Customer',
                'status' => $data['status'] ?? 'Unknown',
                'appointment_id' => $data['appointment_id'] ?? 'Unknown',
                'amount' => $data['amount'] ?? '0.00',
                'reference' => $data['reference'] ?? null,
                'note' => $data['note'] ?? null,
            ];

            // Try to send to user first
            $userEmailSent = $this->sendEmailToUser($userEmail, 
                'Payment Status Update - ' . ($data['service_name'] ?? 'Auto Repair Service'),
                view('emails.payments.status', $emailData)->render(),
                $this->getPaymentStatusTextContent($data)
            );

            // Always send admin notification as well
            $adminNotificationSent = $this->sendAdminPaymentNotification($data);

            return $userEmailSent || $adminNotificationSent;
        } catch (\Exception $e) {
            Log::error('Failed to send payment status email', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send email to user (will fail for unverified emails due to Resend limitation)
     */
    private function sendEmailToUser(string $userEmail, string $subject, string $htmlContent, string $textContent = null): bool
    {
        try {
            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => $this->fromName . ' <' . $this->fromEmail . '>',
                'to' => [$userEmail],
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent,
            ]);

            if ($response->successful()) {
                Log::info('Email sent successfully to user', ['email' => $userEmail]);
                return true;
            } else {
                Log::warning('Failed to send email to user (expected for unverified emails)', [
                    'email' => $userEmail,
                    'status' => $response->status()
                ]);
                return false;
            }
        } catch (\Exception $e) {
            Log::warning('Exception sending email to user (expected for unverified emails)', [
                'email' => $userEmail,
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send admin appointment notification
     */
    private function sendAdminAppointmentNotification(array $data, string $type): bool
    {
        try {
            $userEmail = $data['email'] ?? 'Unknown';
            $customerName = $data['customer_name'] ?? 'Customer';
            $appointmentDate = $data['appointment_date'] ?? 'Unknown';
            $serviceName = $data['service_name'] ?? 'Auto Repair Service';
            
            $subject = "Appointment {$type} - {$customerName} ({$userEmail})";
            
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #0d6efd;'>Appointment {$type} - Admin Notification</h2>
                    <p>An appointment has been {$type} and the user needs to be notified:</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3>Appointment Details:</h3>
                        <p><strong>Customer:</strong> {$customerName}</p>
                        <p><strong>Email:</strong> {$userEmail}</p>
                        <p><strong>Service:</strong> {$serviceName}</p>
                        <p><strong>Date:</strong> {$appointmentDate}</p>
                        <p><strong>Time:</strong> " . ($data['appointment_time'] ?? 'Unknown') . "</p>
                        <p><strong>Status:</strong> " . ($data['status'] ?? 'Unknown') . "</p>
                    </div>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4>Action Required:</h4>
                        <p>Please forward this information to the customer at <strong>{$userEmail}</strong></p>
                        <p>You can copy the appointment details above and send them via your preferred method.</p>
                    </div>
                    
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;' />
                    <p style='font-size: 12px; color: #6c757d;'>
                        This is an automated notification from Auto Repair Shop System.
                    </p>
                </div>
            ";

            $textContent = "Appointment {$type} - Admin Notification\n\n";
            $textContent .= "Customer: {$customerName} ({$userEmail})\n";
            $textContent .= "Service: {$serviceName}\n";
            $textContent .= "Date: {$appointmentDate}\n";
            $textContent .= "Time: " . ($data['appointment_time'] ?? 'Unknown') . "\n";
            $textContent .= "Status: " . ($data['status'] ?? 'Unknown') . "\n\n";
            $textContent .= "Please forward this information to the customer.\n";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => $this->fromName . ' <' . $this->fromEmail . '>',
                'to' => [$this->verifiedEmail],
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send admin appointment notification', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Send admin payment notification
     */
    private function sendAdminPaymentNotification(array $data): bool
    {
        try {
            $userEmail = $data['email'] ?? 'Unknown';
            $customerName = $data['customer_name'] ?? 'Customer';
            $amount = $data['amount'] ?? 'Unknown';
            $status = $data['status'] ?? 'Unknown';
            
            $subject = "Payment Status Update - {$customerName} ({$userEmail})";
            
            $htmlContent = "
                <div style='font-family: Arial, sans-serif; color: #2c3e50; max-width: 600px; margin: 0 auto;'>
                    <h2 style='color: #0d6efd;'>Payment Status Update - Admin Notification</h2>
                    <p>A payment status has been updated and the user needs to be notified:</p>
                    
                    <div style='background: #f8f9fa; padding: 20px; border-radius: 8px; margin: 20px 0;'>
                        <h3>Payment Details:</h3>
                        <p><strong>Customer:</strong> {$customerName}</p>
                        <p><strong>Email:</strong> {$userEmail}</p>
                        <p><strong>Amount:</strong> \${$amount}</p>
                        <p><strong>Status:</strong> {$status}</p>
                        <p><strong>Method:</strong> " . ($data['payment_method'] ?? 'Unknown') . "</p>
                    </div>
                    
                    <div style='background: #fff3cd; padding: 15px; border-radius: 8px; margin: 20px 0;'>
                        <h4>Action Required:</h4>
                        <p>Please forward this information to the customer at <strong>{$userEmail}</strong></p>
                        <p>You can copy the payment details above and send them via your preferred method.</p>
                    </div>
                    
                    <hr style='border: none; border-top: 1px solid #eee; margin: 20px 0;' />
                    <p style='font-size: 12px; color: #6c757d;'>
                        This is an automated notification from Auto Repair Shop System.
                    </p>
                </div>
            ";

            $textContent = "Payment Status Update - Admin Notification\n\n";
            $textContent .= "Customer: {$customerName} ({$userEmail})\n";
            $textContent .= "Amount: \${$amount}\n";
            $textContent .= "Status: {$status}\n";
            $textContent .= "Method: " . ($data['payment_method'] ?? 'Unknown') . "\n\n";
            $textContent .= "Please forward this information to the customer.\n";

            $response = Http::withHeaders([
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ])->post('https://api.resend.com/emails', [
                'from' => $this->fromName . ' <' . $this->fromEmail . '>',
                'to' => [$this->verifiedEmail],
                'subject' => $subject,
                'html' => $htmlContent,
                'text' => $textContent,
            ]);

            return $response->successful();
        } catch (\Exception $e) {
            Log::error('Failed to send admin payment notification', [
                'error' => $e->getMessage()
            ]);
            return false;
        }
    }

    /**
     * Get appointment booked text content
     */
    private function getAppointmentBookedTextContent(array $data): string
    {
        $customerName = $data['customer_name'] ?? 'Customer';
        $appointmentDate = $data['appointment_date'] ?? 'Unknown';
        $appointmentTime = $data['appointment_time'] ?? 'Unknown';
        $serviceName = $data['service_name'] ?? 'Auto Repair Service';
        
        return "Hi {$customerName},\n\n" .
               "Your appointment has been booked successfully.\n\n" .
               "Appointment Details:\n" .
               "Date: {$appointmentDate}\n" .
               "Time: {$appointmentTime}\n" .
               "Service: {$serviceName}\n\n" .
               "Thank you for choosing Auto Repair Shop!";
    }

    /**
     * Get appointment status changed text content
     */
    private function getAppointmentStatusChangedTextContent(array $data): string
    {
        $customerName = $data['customer_name'] ?? 'Customer';
        $status = $data['status'] ?? 'Unknown';
        $appointmentDate = $data['appointment_date'] ?? 'Unknown';
        $serviceName = $data['service_name'] ?? 'Auto Repair Service';
        
        return "Hi {$customerName},\n\n" .
               "Your appointment status has been updated to: {$status}\n\n" .
               "Appointment Details:\n" .
               "Date: {$appointmentDate}\n" .
               "Service: {$serviceName}\n\n" .
               "Thank you for choosing Auto Repair Shop!";
    }

    /**
     * Get payment status text content
     */
    private function getPaymentStatusTextContent(array $data): string
    {
        $customerName = $data['customer_name'] ?? 'Customer';
        $status = $data['status'] ?? 'Unknown';
        $amount = $data['amount'] ?? '0.00';
        $paymentMethod = $data['payment_method'] ?? 'N/A';
        
        return "Hi {$customerName},\n\n" .
               "Your payment status has been updated to: {$status}\n\n" .
               "Payment Details:\n" .
               "Amount: \${$amount}\n" .
               "Method: {$paymentMethod}\n\n" .
               "Thank you for choosing Auto Repair Shop!";
    }
}
