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
 * Notification sent when a new vaccination schedule is created
 * 
 * NOTE: Sends synchronously (no queue) due to production queue issues.
 * The controller uses notifyNow() to bypass queuing entirely.
 * 
 * Notification Channels:
 * - 'database': Stores in notifications table for in-app polling
 * - FcmChannel: Sends push notifications via Firebase
 * 
 * UPDATED: Generic announcement format (no patient-specific data)
 * Each parent receives ONE notification per schedule regardless of number of children
 * 
 * IMPORTANT: Stores schedule data as plain values (not model) to support serialization.
 */
class VaccinationScheduleCreated extends Notification
{
    use Queueable;

    /**
     * Schedule data stored as plain values (not model) to support queue processing
     */
    public int $scheduleId;
    public string $barangay;
    public string $vaccinationDate;
    public ?string $vaccinationTime;
    public ?string $vaccineType;
    public ?string $notes;

    /**
     * Create a new notification instance.
     * 
     * @param mixed $vaccinationSchedule VaccinationSchedule model or array with schedule data
     */
    public function __construct($vaccinationSchedule)
    {
        // Support both model object and array input
        if (is_array($vaccinationSchedule)) {
            $this->scheduleId = $vaccinationSchedule['id'];
            $this->barangay = $vaccinationSchedule['barangay'];
            $this->vaccinationDate = $vaccinationSchedule['vaccination_date'];
            $this->vaccinationTime = $vaccinationSchedule['vaccination_time'] ?? null;
            $this->vaccineType = $vaccinationSchedule['vaccine_type'] ?? null;
            $this->notes = $vaccinationSchedule['notes'] ?? null;
        } else {
            // Extract data from model to plain values
            $this->scheduleId = $vaccinationSchedule->id;
            $this->barangay = $vaccinationSchedule->barangay;
            $this->vaccinationDate = $vaccinationSchedule->vaccination_date instanceof \Carbon\Carbon 
                ? $vaccinationSchedule->vaccination_date->format('Y-m-d')
                : $vaccinationSchedule->vaccination_date;
            $this->vaccinationTime = $vaccinationSchedule->vaccination_time;
            $this->vaccineType = $vaccinationSchedule->vaccine_type;
            $this->notes = $vaccinationSchedule->notes;
        }
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
        
        // Format location - don't use "Brgy." for RHU
        $location = $this->formatBarangayName();
        
        // Generic announcement format - no patient-specific data
        $message = sprintf(
            'May bagong schedule ng pagbabakuna sa %s. Petsa: %s, Oras: %s',
            $location,
            $date,
            $time
        );
        
        // Add notes if available
        if (!empty($this->notes)) {
            $message .= '. Paalala: ' . $this->notes;
        }
        
        return [
            'type' => 'vaccination_schedule_created',
            'title' => 'Bagong Schedule ng Pagbabakuna',
            'message' => $message,
            'vaccination_schedule_id' => $this->scheduleId,
            'vaccination_date' => $this->vaccinationDate,
            'vaccination_time' => $time,
            'barangay' => $this->barangay,
            'vaccine_type' => $this->vaccineType,
            'notes' => $this->notes,
            'icon' => 'calendar',
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
        
        // Format location - don't use "Brgy." for RHU
        $location = $this->formatBarangayName();
        
        // Get current vaccine availability
        $vaccineAvailability = $this->getVaccineAvailabilityText();
        
        $message = sprintf(
            "Maligayang araw! May bagong schedule ng pagbabakuna sa %s.\n\nPetsa: %s\nOras: %s",
            $location,
            $date,
            $time
        );
        
        // Add notes if available
        if (!empty($this->notes)) {
            $message .= "\n\nPaalala: " . $this->notes;
        }
        
        // Only add vaccine availability if there are available vaccines
        if (!empty($vaccineAvailability)) {
            $message .= "\n\n" . $vaccineAvailability;
        }
        
        $message .= "\n\nPumunta sa Health Center sa nakatakdang petsa. Salamat po!";
        
        return $message;
    }
    
    /**
     * Format barangay name for display
     * Adds "Brgy." prefix unless it's RHU
     */
    protected function formatBarangayName(): string
    {
        // Don't add "Brgy." prefix for RHU locations
        if (stripos($this->barangay, 'RHU') !== false || stripos($this->barangay, 'Health Center') !== false) {
            return $this->barangay;
        }
        return 'Brgy. ' . $this->barangay;
    }
    
    /**
     * Get formatted vaccine availability text for SMS
     * Only shows vaccines that are IN STOCK
     * Returns empty string if no vaccines available
     */
    protected function getVaccineAvailabilityText(): string
    {
        try {
            // Only get vaccines with stocks > 0
            $availableVaccines = \App\Models\Vaccine::select('vaccine_name')
                ->where('stocks', '>', 0)
                ->orderBy('vaccine_name')
                ->get();
            
            if ($availableVaccines->isEmpty()) {
                return ''; // Return empty if no vaccines available
            }
            
            $lines = ["Kasalukuyang Available na Bakuna:"];
            foreach ($availableVaccines as $vaccine) {
                $lines[] = "- {$vaccine->vaccine_name}";
            }
            
            return implode("\n", $lines);
        } catch (\Exception $e) {
            return ''; // Return empty on error
        }
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
    //         'May bagong schedule ng bakuna sa %s. Petsa: %s, Oras: %s',
    //         $this->vaccinationSchedule->barangay,
    //         $date,
    //         $time
    //     );
    //     
    //     return (new WebPushMessage())
    //         ->title('Bagong Schedule ng Bakuna')
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
            'May bagong schedule ng pagbabakuna sa %s. Petsa: %s, Oras: %s',
            $location,
            $date,
            $time
        );
        
        // Add notes summary if available (keep FCM body short)
        if (!empty($this->notes)) {
            $body .= '. Paalala: ' . (strlen($this->notes) > 50 ? substr($this->notes, 0, 50) . '...' : $this->notes);
        }
        
        return [
            'title' => 'Bagong Schedule ng Pagbabakuna',
            'body' => $body,
            'data' => [
                'type' => 'vaccination_schedule_created',
                'schedule_id' => (string) $this->scheduleId,
                'url' => route('parent.dashboard'),
            ]
        ];
    }


}
