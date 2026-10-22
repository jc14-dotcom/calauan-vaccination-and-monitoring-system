<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use NotificationChannels\WebPush\WebPushChannel;
use NotificationChannels\WebPush\WebPushMessage;
use App\Channels\FcmChannel;

/**
 * FeedbackRequest Notification
 * 
 * DISABLED: This notification is currently disabled.
 * Both In-app and SMS notifications are disabled by returning empty channels.
 * To re-enable, uncomment the channels in the via() method.
 */
class FeedbackRequest extends Notification // implements ShouldQueue
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public function __construct(
        public $patient,
        public $vaccineRecord
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
            'type' => 'feedback_request',
            'title' => 'Pakibahagi ang Iyong Karanasan',
            'message' => sprintf(
                'Salamat sa pagpabakuna kay %s! Sana ay maibahagi ninyo ang inyong karanasan sa aming serbisyo. Ang inyong feedback ay makakatulong sa amin na mapabuti ang aming paglilingkod.',
                $this->patient->name
            ),
            'patient_id' => $this->patient->id,
            'patient_name' => $this->patient->name,
            'vaccine_record_id' => $this->vaccineRecord->id,
            'vaccine_type' => $this->vaccineRecord->vaccine_type,
            'icon' => 'message-square',
            'action_url' => route('parent.dashboard'),
        ];
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        return sprintf(
            "Salamat sa pagpabakuna kay %s!\n\nMaari po ba kayong magbigay ng feedback tungkol sa inyong karanasan? Bisitahin ang aming website at punan ang feedback form.\n\nSalamat!",
            $this->patient->name
        );
    }

    /**
     * Get the WebPush message.
     */
    public function toWebPush(object $notifiable): WebPushMessage
    {
        return (new WebPushMessage())
            ->title('Pakibahagi ang Iyong Karanasan')
            ->icon('/images/icon-192x192.png')
            ->body(sprintf(
                'Salamat sa pagpabakuna kay %s! Maibahagi ninyo ang inyong karanasan sa aming serbisyo.',
                $this->patient->name
            ))
            ->action('Magbigay ng Feedback', route('parent.dashboard'))
            ->data(['vaccine_record_id' => $this->vaccineRecord->id]);
    }

    /**
     * Get the FCM message.
     */
    public function toFcm(object $notifiable): array
    {
        return [
            'title' => 'Pakibahagi ang Iyong Karanasan',
            'body' => sprintf(
                'Salamat sa pagpabakuna kay %s! Maibahagi ninyo ang inyong karanasan sa aming serbisyo.',
                $this->patient->name
            ),
            'data' => [
                'type' => 'feedback_request',
                'patient_id' => (string) $this->patient->id,
                'patient_name' => $this->patient->name,
                'vaccine_record_id' => (string) $this->vaccineRecord->id,
                'action_url' => route('parent.dashboard'),
            ],
        ];
    }
}
