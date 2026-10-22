<?php

namespace App\Channels;

use App\Services\FcmService;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Log;

class FcmChannel
{
    protected $fcm;

    public function __construct(FcmService $fcm)
    {
        $this->fcm = $fcm;
    }

    /**
     * Send the given notification
     *
     * @param  mixed  $notifiable
     * @param  \Illuminate\Notifications\Notification  $notification
     * @return void
     */
    public function send($notifiable, Notification $notification)
    {
        // Get FCM token from the notifiable entity (parent)
        $fcmToken = $notifiable->fcm_token;
        
        if (!$fcmToken) {
            Log::info('FCM: No token found for notifiable', [
                'notifiable_id' => $notifiable->id,
                'notifiable_type' => get_class($notifiable)
            ]);
            return;
        }

        // Validate token format
        if (!$this->fcm->isValidToken($fcmToken)) {
            Log::warning('FCM: Invalid token format', [
                'notifiable_id' => $notifiable->id,
                'token_length' => strlen($fcmToken)
            ]);
            return;
        }

        // Get notification data from the toFcm method
        $data = $notification->toFcm($notifiable);
        
        if (!$data) {
            Log::warning('FCM: No data returned from toFcm method', [
                'notification' => get_class($notification)
            ]);
            return;
        }

        // Send the notification via FCM
        return $this->fcm->send(
            $fcmToken,
            $data['title'] ?? 'Notification',
            $data['body'] ?? '',
            $data['data'] ?? []
        );
    }
}
