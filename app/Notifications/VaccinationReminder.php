<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
// LEGACY: WebPush/VAPID - Replaced by FCM (Firebase Cloud Messaging)
// use NotificationChannels\WebPush\WebPushChannel;
// use NotificationChannels\WebPush\WebPushMessage;
use App\Channels\FcmChannel;
use Carbon\Carbon;

/**
 * Notification sent as a reminder X days before vaccination
 * 
 * Uses database queue for async processing.
 * 
 * IMPORTANT: Stores schedule and patient data as plain values (not models) to support database queue.
 * When using database queue, models are serialized by ID and reloaded later.
 * If models are deleted or changed, it could fail. By storing plain values, notifications are reliable.
 */
class VaccinationReminder extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Schedule data stored as plain values
     */
    public int $scheduleId;
    public string $vaccinationDate;
    public ?string $vaccinationTime;
    public ?string $vaccineType;
    public string $barangay;
    public ?string $notes;

    /**
     * Patient data stored as plain values
     */
    public int $patientId;
    public string $patientName;

    /**
     * Days until vaccination
     */
    public int $daysUntil;

    /**
     * Create a new notification instance.
     * 
     * @param mixed $vaccinationSchedule VaccinationSchedule model or array
     * @param mixed $patient Patient model or array
     * @param int $daysUntil Days until vaccination
     */
    public function __construct($vaccinationSchedule, $patient, $daysUntil)
    {
        // Extract schedule data
        if (is_array($vaccinationSchedule)) {
            $this->scheduleId = $vaccinationSchedule['id'];
            $this->vaccinationDate = $vaccinationSchedule['vaccination_date'];
            $this->vaccinationTime = $vaccinationSchedule['vaccination_time'] ?? null;
            $this->vaccineType = $vaccinationSchedule['vaccine_type'] ?? null;
            $this->barangay = $vaccinationSchedule['barangay'];
            $this->notes = $vaccinationSchedule['notes'] ?? null;
        } else {
            $this->scheduleId = $vaccinationSchedule->id;
            $this->vaccinationDate = $vaccinationSchedule->vaccination_date instanceof Carbon 
                ? $vaccinationSchedule->vaccination_date->format('Y-m-d')
                : $vaccinationSchedule->vaccination_date;
            $this->vaccinationTime = $vaccinationSchedule->vaccination_time;
            $this->vaccineType = $vaccinationSchedule->vaccine_type;
            $this->barangay = $vaccinationSchedule->barangay;
            $this->notes = $vaccinationSchedule->notes ?? null;
        }

        // Extract patient data
        if (is_array($patient)) {
            $this->patientId = $patient['id'];
            $this->patientName = $patient['name'];
        } else {
            $this->patientId = $patient->id;
            $this->patientName = $patient->name;
        }

        $this->daysUntil = $daysUntil;
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
        $daysText = $this->daysUntil == 1 ? 'bukas' : $this->daysUntil . ' araw';
        $date = \Carbon\Carbon::parse($this->vaccinationDate)->format('F d, Y');
        $time = $this->vaccinationTime ?: '7:00 AM';
        $location = $this->formatBarangayName();
        
        return [
            'type' => 'vaccination_reminder',
            'title' => 'Paalala: Malapit na ang Bakuna',
            'message' => sprintf(
                'Paalala: Malapit na ang schedule ng pagbabakuna sa %s (%s). Petsa: %s, Oras: %s. Huwag kalimutang dalhin ang vaccination card ng inyong anak.',
                $location,
                $daysText,
                $date,
                $time
            ),
            'vaccination_schedule_id' => $this->scheduleId,
            'vaccination_date' => $this->vaccinationDate,
            'vaccination_time' => $this->vaccinationTime,
            'vaccine_type' => $this->vaccineType,
            'barangay' => $this->barangay,
            'days_until' => $this->daysUntil,
            'icon' => 'bell',
            'action_url' => route('parent.dashboard'),
        ];
    }

    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        $daysText = $this->daysUntil == 1 ? 'BUKAS na ito' : $this->daysUntil . ' ARAW na lang';
        $date = \Carbon\Carbon::parse($this->vaccinationDate)->format('F d, Y');
        $time = $this->vaccinationTime ?: '7:00 AM';
        $location = $this->formatBarangayName();
        
        // Get current vaccine availability
        $vaccineAvailability = $this->getVaccineAvailabilityText();
        
        $message = sprintf(
            "PAALALA: Malapit na ang schedule ng pagbabakuna sa %s (%s)!\n\nPetsa: %s\nOras: %s",
            $location,
            $daysText,
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
        
        $message .= "\n\nHuwag kalimutang dalhin ang vaccination card ng inyong anak. Salamat po!";
        
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
    //     $daysText = $this->daysUntil == 1 ? 'bukas' : $this->daysUntil . ' araw';
    //     
    //     return (new WebPushMessage())
    //         ->title('Paalala: Malapit na ang Bakuna')
    //         ->icon('/images/icon-192x192.png')
    //         ->body(sprintf(
    //             'Ang bakuna para kay %s ay %s na. Petsa: %s, Oras: %s',
    //             $this->patient->name,
    //             $daysText,
    //             $this->vaccinationSchedule->vaccination_date,
    //             $this->vaccinationSchedule->vaccination_time
    //         ))
    //         ->action('Tingnan', route('parent.dashboard'))
    //         ->data(['id' => $this->vaccinationSchedule->id])
    //         ->badge('/images/badge.png');
    // }

    /**
     * Get the FCM message.
     */
    public function toFcm(object $notifiable): array
    {
        $daysText = $this->daysUntil == 1 ? 'bukas' : $this->daysUntil . ' araw';
        $date = \Carbon\Carbon::parse($this->vaccinationDate)->format('F d, Y');
        $time = $this->vaccinationTime ?: '7:00 AM';
        $location = $this->formatBarangayName();
        
        return [
            'title' => 'Paalala: Malapit na ang Pagbabakuna',
            'body' => sprintf(
                'Malapit na ang schedule ng pagbabakuna sa %s (%s). Petsa: %s, Oras: %s',
                $location,
                $daysText,
                $date,
                $time
            ),
            'data' => [
                'type' => 'vaccination_reminder',
                'vaccination_schedule_id' => (string) $this->scheduleId,
                'vaccination_date' => $this->vaccinationDate,
                'barangay' => $this->barangay,
                'days_until' => (string) $this->daysUntil,
                'action_url' => route('parent.dashboard'),
            ],
        ];
    }
}
