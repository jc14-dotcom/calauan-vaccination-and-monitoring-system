<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Channels\FcmChannel;

/**
 * LowStockAlert Notification
 * 
 * DISABLED: This notification is currently disabled.
 * Both In-app and SMS notifications are disabled by returning empty channels.
 * To re-enable, uncomment the channels in the via() method.
 */
class LowStockAlert extends Notification // implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $vaccine,
        public $currentStock,
        public $threshold
    ) {}

    /**
     * Get the notification's delivery channels.
     * 
     * DISABLED: Returns empty array to disable all notification channels.
     * To re-enable, uncomment the return statement below.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        // DISABLED - Uncomment below to re-enable notifications
        // return ['database', FcmChannel::class];
        
        return []; // No channels - notifications disabled
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'low_stock_alert',
            'title' => 'Babala: Mababa ang Stock ng Bakuna',
            'message' => sprintf(
                'Ang stock ng %s ay mababa na. Kasalukuyang stock: %d doses. Threshold: %d doses. Mag-order na ng panibagong supply.',
                $this->vaccine->vaccine_name ?? $this->vaccine,
                $this->currentStock,
                $this->threshold
            ),
            'vaccine_name' => $this->vaccine->vaccine_name ?? $this->vaccine,
            'current_stock' => $this->currentStock,
            'threshold' => $this->threshold,
            'icon' => 'alert-triangle',
            'action_url' => route('health_worker.inventory'),
            'priority' => 'high',
        ];
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        return sprintf(
            "BABALA: Mababa ang stock ng bakuna!\n\nBakuna: %s\nKasalukuyang Stock: %d doses\nThreshold: %d doses\n\nKailangan na ng restock. Mag-order agad.",
            $this->vaccine->vaccine_name ?? $this->vaccine,
            $this->currentStock,
            $this->threshold
        );
    }

    /**
     * Get the WebPush message.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Babala: Mababa ang Stock ng Bakuna')
            ->icon('/images/icon-192x192.png')
            ->body(sprintf(
                'Ang stock ng %s ay mababa na. Kasalukuyang stock: %d doses. Mag-order na.',
                $this->vaccine->vaccine_name ?? $this->vaccine,
                $this->currentStock
            ))
            ->action('Tingnan', route('health_worker.inventory'))
            ->data(['vaccine' => $this->vaccine->vaccine_name ?? $this->vaccine, 'stock' => $this->currentStock])
            ->badge('/images/badge.png');
    }

    /**
     * Get the FCM message.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Babala: Mababa ang Stock ng Bakuna',
            'body' => sprintf(
                'Ang stock ng %s ay mababa na. Kasalukuyang stock: %d doses. Mag-order na.',
                $this->vaccine->vaccine_name ?? $this->vaccine,
                $this->currentStock
            ),
            'data' => [
                'type' => 'low_stock_alert',
                'vaccine_name' => $this->vaccine->vaccine_name ?? $this->vaccine,
                'current_stock' => (string) $this->currentStock,
                'threshold' => (string) $this->threshold,
                'priority' => 'high',
                'action_url' => route('health_worker.inventory'),
            ],
        ];
    }
}
