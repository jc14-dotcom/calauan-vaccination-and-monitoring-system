<?php

namespace App\Services\Notification;

use App\Models\SmsLog;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SmsService
{
    protected $enabled;
    protected $apiKey;
    protected $senderName;
    protected $apiUrl;

    public function __construct()
    {
        $this->enabled = config('sms.enabled', false);
        $this->apiKey = config('sms.semaphore.api_key');
        $this->senderName = config('sms.semaphore.sender_name', 'HealthCtr');
        $this->apiUrl = config('sms.semaphore.api_url', 'https://api.semaphore.co/api/v4/messages');
    }

    /**
     * Send SMS message
     */
    public function send(string $phoneNumber, string $message, $notifiable = null, $notificationId = null): array
    {
        // If SMS is disabled, just log it
        if (!$this->enabled) {
            $this->logSms($phoneNumber, $message, 'disabled', null, null, $notifiable, $notificationId);
            
            return [
                'success' => false,
                'message' => 'SMS service is disabled',
                'cost' => 0,
            ];
        }

        // Validate configuration
        if (empty($this->apiKey)) {
            Log::error('SMS API key is not configured');
            
            return [
                'success' => false,
                'message' => 'SMS API key is not configured',
                'cost' => 0,
            ];
        }

        // Format phone number (ensure it starts with +63 for Philippines)
        $formattedPhone = $this->formatPhoneNumber($phoneNumber);

        try {
            // Send SMS via Semaphore API
            // Note: withoutVerifying() handles SSL certificate issues in local development
            $response = Http::withoutVerifying()->post($this->apiUrl, [
                'apikey' => $this->apiKey,
                'number' => $formattedPhone,
                'message' => $message,
                'sendername' => $this->senderName,
            ]);

            $responseData = $response->json();

            if ($response->successful() && isset($responseData[0]['message_id'])) {
                // Calculate estimated cost (₱0.50-0.85 per SMS, we'll use average ₱0.65)
                $messageLength = strlen($message);
                $smsCount = ceil($messageLength / 160);
                $estimatedCost = $smsCount * 0.65;

                $this->logSms(
                    $formattedPhone,
                    $message,
                    'sent',
                    json_encode($responseData),
                    $responseData[0]['message_id'],
                    $notifiable,
                    $notificationId,
                    $estimatedCost
                );

                return [
                    'success' => true,
                    'message' => 'SMS sent successfully',
                    'message_id' => $responseData[0]['message_id'],
                    'cost' => $estimatedCost,
                ];
            } else {
                $this->logSms(
                    $formattedPhone,
                    $message,
                    'failed',
                    json_encode($responseData),
                    null,
                    $notifiable,
                    $notificationId
                );

                return [
                    'success' => false,
                    'message' => $responseData['message'] ?? 'Failed to send SMS',
                    'cost' => 0,
                ];
            }
        } catch (\Exception $e) {
            Log::error('SMS sending failed: ' . $e->getMessage());
            
            $this->logSms(
                $formattedPhone,
                $message,
                'failed',
                $e->getMessage(),
                null,
                $notifiable,
                $notificationId
            );

            return [
                'success' => false,
                'message' => 'SMS sending failed: ' . $e->getMessage(),
                'cost' => 0,
            ];
        }
    }

    /**
     * Format phone number to international format
     */
    protected function formatPhoneNumber(string $phoneNumber): string
    {
        // Remove all non-numeric characters
        $cleaned = preg_replace('/[^0-9]/', '', $phoneNumber);

        // If it starts with 0, replace with 63
        if (substr($cleaned, 0, 1) === '0') {
            $cleaned = '63' . substr($cleaned, 1);
        }

        // If it doesn't start with 63, prepend it
        if (substr($cleaned, 0, 2) !== '63') {
            $cleaned = '63' . $cleaned;
        }

        return $cleaned;
    }

    /**
     * Log SMS to database
     */
    protected function logSms(
        string $phoneNumber,
        string $message,
        string $status,
        ?string $gatewayResponse,
        ?string $messageId,
        $notifiable = null,
        $notificationId = null,
        ?float $cost = null
    ): void {
        $logData = [
            'recipient_phone' => $phoneNumber,
            'message' => $message,
            'status' => $status,
            'gateway_response' => $gatewayResponse,
            'gateway_message_id' => $messageId,
            'notification_id' => $notificationId,
            'cost' => $cost,
            'sent_at' => $status === 'sent' ? now() : null,
        ];

        if ($notifiable) {
            $logData['notifiable_type'] = get_class($notifiable);
            $logData['notifiable_id'] = $notifiable->id;
        }

        SmsLog::create($logData);
    }

    /**
     * Check if SMS service is enabled
     */
    public function isEnabled(): bool
    {
        return $this->enabled;
    }

    /**
     * Get SMS statistics
     */
    public function getStatistics(?\DateTime $from = null, ?\DateTime $to = null): array
    {
        $query = SmsLog::query();

        if ($from) {
            $query->where('created_at', '>=', $from);
        }

        if ($to) {
            $query->where('created_at', '<=', $to);
        }

        return [
            'total_sent' => $query->clone()->where('status', 'sent')->count(),
            'total_failed' => $query->clone()->where('status', 'failed')->count(),
            'total_cost' => $query->clone()->where('status', 'sent')->sum('cost'),
            'pending' => $query->clone()->where('status', 'pending')->count(),
        ];
    }
}
