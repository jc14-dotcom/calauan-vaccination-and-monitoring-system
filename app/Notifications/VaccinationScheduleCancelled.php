<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
// LEGACY: WebPush/VAPID - Replaced by FCM (Firebase Cloud Messaging)
// use NotificationChannels\WebPush\WebPushChannel;
// use NotificationChannels\WebPush\WebPushMessage;
use App\Channels\FcmChannel;

/**
 * Notification sent when a vaccination schedule is cancelled
 * 
 * Uses database queue for async processing:
 * - Prevents blocking the HTTP request
 * - Allows retry on failure
 * - Better user experience
 * 
 * Notification Channels:
 * - 'database': Stores in notifications table for in-app polling
 * - FcmChannel: Sends push notifications via Firebase
 * 
 * UPDATED: Generic announcement format (no patient-specific data)
 * Each parent receives ONE notification per schedule regardless of number of children
 * 
 * IMPORTANT: Stores schedule data as plain values (not model) to support database queue
 * When using database queue, models are serialized by ID and reloaded later.
 * If the model is deleted before queue processes, it would fail.
 * By storing plain values, the notification works even after schedule deletion.
 */
class VaccinationScheduleCancelled extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Schedule data stored as plain values (not model) to support queue processing
     * after schedule deletion
     */
    public int $scheduleId;
    public string $barangay;
    public string $vaccinationDate;
    public ?string $vaccinationTime;
    public ?string $vaccineType;
    public ?string $reason;

    /**
     * Create a new notification instance.
     * 
     * @param mixed $vaccinationSchedule VaccinationSchedule model or array with schedule data
     * @param string|null $reason Cancellation reason
     */
    public function __construct($vaccinationSchedule, $reason = null)
    {
        // Support both model object and array input
        if (is_array($vaccinationSchedule)) {
            $this->scheduleId = $vaccinationSchedule['id'];
            $this->barangay = $vaccinationSchedule['barangay'];
            $this->vaccinationDate = $vaccinationSchedule['vaccination_date'];
            $this->vaccinationTime = $vaccinationSchedule['vaccination_time'] ?? null;
            $this->vaccineType = $vaccinationSchedule['vaccine_type'] ?? null;
        } else {
            // Extract data from model to plain values
            $this->scheduleId = $vaccinationSchedule->id;
            $this->barangay = $vaccinationSchedule->barangay;
            $this->vaccinationDate = $vaccinationSchedule->vaccination_date instanceof \Carbon\Carbon 
                ? $vaccinationSchedule->vaccination_date->format('Y-m-d')
                : $vaccinationSchedule->vaccination_date;
            $this->vaccinationTime = $vaccinationSchedule->vaccination_time;
            $this->vaccineType = $vaccinationSchedule->vaccine_type;
        }
        
        $this->reason = $reason;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', FcmChannel::class];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        $date = \Carbon\Carbon::parse($this->vaccinationDate)->format('F d, Y');
        $time = $this->vaccinationTime ?: '7:00 AM';
        $location = $this->formatBarangayName();
        
        // Generic announcement format - no patient-specific data
        $message = sprintf(
            'Ang schedule ng pagbabakuna sa %s ay nakansela. Petsa: %s%s',
            $location,
            $date,
            $this->reason ? '. Dahilan: ' . $this->reason : ''
        );
        
        return [
            'type' => 'vaccination_schedule_cancelled',
            'title' => 'Nakansela ang Schedule ng Bakuna',
            'message' => $message,
            'vaccination_schedule_id' => $this->scheduleId,
            'vaccination_date' => $this->vaccinationDate,
            'barangay' => $this->barangay,
            'vaccine_type' => $this->vaccineType,
            'reason' => $this->reason,
            'icon' => 'x-circle',
            'action_url' => route('parent.dashboard'),
        ];
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        $date = \Carbon\Carbon::parse($this->vaccinationDate)->format('F d, Y');
        $time = $this->vaccinationTime ?: '7:00 AM';
        $location = $this->formatBarangayName();
        
        $message = sprintf(
            "ABISO: Ang naka-schedule na pagbabakuna sa %s para sa %s ay NAKANSELA.",
            $location,
            $date
        );

        if ($this->reason) {
            $message .= "\n\nDahilan: " . $this->reason;
        }

        $message .= "\n\nMag-antay lamang po ng anunsyo para sa mga susunod na schedule ng pagbabakuna. Salamat po!";

        return $message;
    }
    
    /**
     * Format barangay name for display
     * Adds "Brgy." prefix unless it's RHU
     */
    protected function formatBarangayName(): string
    {
        if (stripos($this->barangay, 'RHU') !== false || stripos($this->barangay, 'Health Center') !== false) {
            return $this->barangay;
        }
        return 'Brgy. ' . $this->barangay;
    }

    /**
     * LEGACY: WebPush/VAPID method - Replaced by FCM (Firebase Cloud Messaging)
     * Kept for reference in case rollback is needed
     */
    // public function toWebPush(object $notifiable): WebPushMessage
    // {
    //     $date = \Carbon\Carbon::parse($this->vaccinationSchedule->vaccination_date)->format('F d, Y');
    //     $time = $this->vaccinationSchedule->vaccination_time ?: '7:00 AM';
    //     
    //     $body = sprintf(
    //         'Ang schedule ng bakuna sa %s ay nakansela. Petsa: %s, Oras: %s',
    //         $this->vaccinationSchedule->barangay,
    //         $date,
    //         $time
    //     );
    //
    //     if ($this->reason) {
    //         $body .= '. Dahilan: ' . $this->reason;
    //     }
    //
    //     return (new WebPushMessage())
    //         ->title('Nakansela ang Schedule ng Bakuna')
    //         ->icon('/images/icon-192x192.png')
    //         ->body($body)
    //         ->action('Tingnan', route('parent.dashboard'))
    //         ->data(['id' => $this->vaccinationSchedule->id]);
    // }

    /**
     * Get the FCM message.
     */
    public function toFcm(object $notifiable): array
    {
        $date = \Carbon\Carbon::parse($this->vaccinationDate)->format('F d, Y');
        $time = $this->vaccinationTime ?: '7:00 AM';
        $location = $this->formatBarangayName();
        
        $body = sprintf(
            'Ang schedule ng pagbabakuna sa %s ay nakansela. Petsa: %s, Oras: %s',
            $location,
            $date,
            $time
        );

        if ($this->reason) {
            $body .= '. Dahilan: ' . $this->reason;
        }

        return [
            'title' => 'Nakansela ang Schedule ng Pagbabakuna',
            'body' => $body,
            'data' => [
                'type' => 'vaccination_schedule_cancelled',
                'vaccination_schedule_id' => (string) $this->scheduleId,
                'vaccination_date' => $this->vaccinationDate,
                'barangay' => $this->barangay,
                'action_url' => route('parent.dashboard'),
            ],
        ];
    }
}
