<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Http;
use Exception;

class SmsService
{
    protected string $provider;
    protected array $config;

    public function __construct()
    {
        $this->provider = config('services.sms.provider', 'twilio');
        $this->config = config('services.sms.' . $this->provider, []);
    }

    /**
     * Send SMS message
     */
    public function send(string $to, string $message): bool
    {
        try {
            // Clean phone number
            $to = $this->cleanPhoneNumber($to);
            
            if (!$this->isValidPhoneNumber($to)) {
                Log::warning('Invalid phone number for SMS', ['phone' => $to]);
                return false;
            }

            return match ($this->provider) {
                'twilio' => $this->sendViaTwilio($to, $message),
                'textlocal' => $this->sendViaTextLocal($to, $message),
                'log' => $this->sendViaLog($to, $message),
                default => false,
            };
        } catch (Exception $e) {
            Log::error('SMS sending failed', [
                'to' => $to,
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send SMS via Twilio
     */
    protected function sendViaTwilio(string $to, string $message): bool
    {
        if (empty($this->config['sid']) || empty($this->config['token']) || empty($this->config['from'])) {
            Log::warning('Twilio configuration missing');
            return false;
        }

        try {
            $response = Http::withBasicAuth($this->config['sid'], $this->config['token'])
                ->asForm()
                ->post(
                    "https://api.twilio.com/2010-04-01/Accounts/{$this->config['sid']}/Messages.json",
                    [
                        'From' => $this->config['from'],
                        'To' => $to,
                        'Body' => $message,
                    ]
                );

            if ($response->successful()) {
                Log::info('SMS sent via Twilio', [
                    'to' => $to,
                    'sid' => $response->json('sid'),
                ]);
                return true;
            }

            Log::error('Twilio SMS failed', [
                'response' => $response->json(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('Twilio SMS exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Send SMS via TextLocal
     */
    protected function sendViaTextLocal(string $to, string $message): bool
    {
        if (empty($this->config['apikey']) || empty($this->config['sender'])) {
            Log::warning('TextLocal configuration missing');
            return false;
        }

        try {
            $response = Http::asForm()->post('https://api.textlocal.in/send/', [
                'apikey' => $this->config['apikey'],
                'sender' => $this->config['sender'],
                'numbers' => $to,
                'message' => $message,
            ]);

            if ($response->successful() && $response->json('status') === 'success') {
                Log::info('SMS sent via TextLocal', [
                    'to' => $to,
                ]);
                return true;
            }

            Log::error('TextLocal SMS failed', [
                'response' => $response->json(),
            ]);
            return false;
        } catch (Exception $e) {
            Log::error('TextLocal SMS exception', [
                'error' => $e->getMessage(),
            ]);
            return false;
        }
    }

    /**
     * Log SMS for development
     */
    protected function sendViaLog(string $to, string $message): bool
    {
        Log::info('SMS Message (Development)', [
            'to' => $to,
            'message' => $message,
        ]);
        return true;
    }

    /**
     * Clean phone number
     */
    protected function cleanPhoneNumber(string $phone): string
    {
        // Remove all non-numeric characters
        $phone = preg_replace('/[^0-9]/', '', $phone);
        
        // Add US country code if not present
        if (strlen($phone) === 10) {
            $phone = '1' . $phone;
        }
        
        // Add + prefix for international format
        if (!str_starts_with($phone, '+')) {
            $phone = '+' . $phone;
        }
        
        return $phone;
    }

    /**
     * Validate phone number
     */
    protected function isValidPhoneNumber(string $phone): bool
    {
        // Basic validation for US phone numbers
        return preg_match('/^\+1[0-9]{10}$/', $phone) === 1;
    }

    /**
     * Send bulk SMS
     */
    public function sendBulk(array $recipients, string $message): array
    {
        $results = [
            'success' => [],
            'failed' => [],
        ];

        foreach ($recipients as $recipient) {
            $phone = is_array($recipient) ? ($recipient['phone'] ?? null) : $recipient;
            
            if ($phone && $this->send($phone, $message)) {
                $results['success'][] = $phone;
            } else {
                $results['failed'][] = $phone;
            }
        }

        return $results;
    }
}