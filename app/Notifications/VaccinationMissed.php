<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;
use App\Channels\FcmChannel;
use App\Models\VaccinationSchedule;

/**
 * Notification sent when a patient misses their vaccination appointment
 * 
 * IMPORTANT: Stores schedule data as plain values (not model) to support database queue.
 * When using database queue, models are serialized by ID and reloaded later.
 * By storing plain values, notifications work even if schedule is modified/deleted.
 */
class VaccinationMissed extends Notification implements ShouldQueue
{
    use Queueable;

    /**
     * Schedule data stored as plain values (not model) to support queue processing
     */
    protected int $scheduleId;
    protected string $barangay;
    protected string $scheduledDate;

    /**
     * Create a new notification instance.
     * 
     * @param VaccinationSchedule|array $schedule VaccinationSchedule model or array with schedule data
     */
    public function __construct($schedule)
    {
        if (is_array($schedule)) {
            $this->scheduleId = $schedule['id'];
            $this->barangay = $schedule['barangay'];
            $this->scheduledDate = $schedule['scheduled_date'] ?? $schedule['vaccination_date'];
        } else {
            $this->scheduleId = $schedule->id;
            $this->barangay = $schedule->barangay;
            $this->scheduledDate = $schedule->vaccination_date instanceof \Carbon\Carbon 
                ? $schedule->vaccination_date->format('F j, Y')
                : \Carbon\Carbon::parse($schedule->vaccination_date)->format('F j, Y');
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
        $location = $this->formatBarangayName();
        return [
            'type' => 'vaccination_missed',
            'title' => 'Paalala: Hindi Nakarating sa Pagbabakuna',
            'message' => "Batay sa aming talaan, may schedule ng pagbabakuna noong {$this->scheduledDate} sa {$location} na hindi napuntahan. Mag-antay lamang ng anunsyo para sa mga susunod na schedule ng pagbabakuna.",
            'barangay' => $this->barangay,
            'scheduled_date' => $this->scheduledDate,
            'schedule_id' => $this->scheduleId,
            'action_url' => route('parent.dashboard'),
            'icon' => 'calendar-x',
            'priority' => 'high',
        ];
    }

    /**
     * Get the FCM message.
     */
    public function toFcm(object $notifiable): array
    {
        $location = $this->formatBarangayName();
        return [
            'title' => 'Paalala: Hindi Nakarating sa Pagbabakuna',
            'body' => "Batay sa aming talaan, may schedule ng pagbabakuna noong {$this->scheduledDate} sa {$location} na hindi napuntahan. Mag-antay lamang ng anunsyo para sa susunod na schedule.",
            'data' => [
                'type' => 'vaccination_missed',
                'schedule_id' => (string) $this->scheduleId,
                'url' => route('parent.dashboard'),
            ],
        ];
    }
    
    /**
     * Get the SMS message.
     */
    public function toSms(object $notifiable): string
    {
        $location = $this->formatBarangayName();
        
        return sprintf(
            "PAALALA: Batay sa aming talaan, may schedule ng pagbabakuna noong %s sa %s na hindi napuntahan.\n\nMag-antay lamang po ng anunsyo para sa mga susunod na schedule ng pagbabakuna. Salamat po!",
            $this->scheduledDate,
            $location
        );
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
}
