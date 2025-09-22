<?php

namespace App\Services;

class SmsService
{
    /**
     * Send an SMS message using Semaphore API if credentials are configured.
     * Falls back to no-op if env vars are missing.
     */
    public function send(string $toPhoneE164, string $message): bool
    {
        $apiKey = env('SEMAPHORE_API_KEY');
        $senderName = env('SEMAPHORE_SENDER_NAME', 'AutoRepair');

        if (!$apiKey) {
            // Not configured; skip sending
            \Log::warning('SMS not sent: Semaphore API key not configured', [
                'api_key_set' => !empty($apiKey),
                'to' => $toPhoneE164,
                'message' => $message
            ]);
            return false;
        }

        try {
            \Log::info('Attempting to send SMS via Semaphore', [
                'to' => $toPhoneE164,
                'sender_name' => $senderName,
                'message_length' => strlen($message)
            ]);

            // Convert E.164 format to local format for Semaphore
            $localNumber = $this->convertToLocalFormat($toPhoneE164);
            
            $response = $this->sendSemaphoreSms($localNumber, $message, $senderName, $apiKey);
            
            // Semaphore returns an array of messages, check if first message has message_id
            if ($response && is_array($response) && isset($response[0]['message_id'])) {
                \Log::info('SMS sent successfully via Semaphore', [
                    'to' => $toPhoneE164,
                    'local_number' => $localNumber,
                    'message_id' => $response[0]['message_id'],
                    'status' => $response[0]['status'] ?? 'unknown'
                ]);
                return true;
            } else {
                \Log::error('SMS send failed: Invalid response from Semaphore', [
                    'to' => $toPhoneE164,
                    'response' => $response
                ]);
                return false;
            }
            
        } catch (\Throwable $e) {
            // General errors
            \Log::error('SMS send failed (Semaphore error): ' . $e->getMessage(), [
                'error_type' => get_class($e),
                'to' => $toPhoneE164,
                'message' => $message,
                'trace' => $e->getTraceAsString()
            ]);
            return false;
        }
    }

    /**
     * Send SMS via Semaphore API
     */
    private function sendSemaphoreSms(string $number, string $message, string $senderName, string $apiKey): ?array
    {
        $url = 'https://api.semaphore.co/api/v4/messages';
        
        $data = [
            'apikey' => $apiKey,
            'number' => $number,
            'message' => $message,
            'sendername' => $senderName
        ];

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($data));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/x-www-form-urlencoded'
        ]);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, true);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($error) {
            throw new \Exception('cURL Error: ' . $error);
        }

        if ($httpCode !== 200) {
            throw new \Exception('HTTP Error: ' . $httpCode . ' - ' . $response);
        }

        $decodedResponse = json_decode($response, true);
        
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new \Exception('Invalid JSON response: ' . $response);
        }

        return $decodedResponse;
    }

    /**
     * Convert E.164 format to local format for Semaphore
     */
    private function convertToLocalFormat(string $e164Number): string
    {
        // Remove + sign
        $number = ltrim($e164Number, '+');
        
        // If it's a Philippines number (+63), convert to local format (09xx)
        if (strpos($number, '63') === 0 && strlen($number) === 12) {
            return '0' . substr($number, 2);
        }
        
        // For other countries, return as is (without +)
        return $number;
    }

    /**
     * Best-effort phone normalization to E.164. Assumes PH by default (+63) if starts with 0.
     */
    public function toE164(?string $raw, string $defaultCountry = '+63'): ?string
    {
        if (!$raw) { return null; }
        $digits = preg_replace('/[^0-9+]/', '', $raw);
        if (!$digits) { return null; }
        if (strpos($digits, '+') === 0) { return $digits; }
        if (strpos($digits, '0') === 0) { return $defaultCountry . substr($digits, 1); }
        return $defaultCountry . $digits;
    }
}


