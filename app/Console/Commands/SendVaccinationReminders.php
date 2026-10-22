<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VaccinationSchedule;
use App\Models\Patient;
use App\Notifications\VaccinationReminder;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class SendVaccinationReminders extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'notifications:send-vaccination-reminders';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Send vaccination reminder notifications (3 days and 1 day before vaccination date)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking for vaccination schedules that need reminders...');
        
        $today = Carbon::today();
        $threeDaysFromNow = $today->copy()->addDays(3);
        $oneDayFromNow = $today->copy()->addDays(1);
        
        $remindersSent = 0;
        
        // Find schedules 3 days from now
        $this->sendRemindersForDate($threeDaysFromNow, 3, $remindersSent);
        
        // Find schedules 1 day from now
        $this->sendRemindersForDate($oneDayFromNow, 1, $remindersSent);
        
        $this->info("Vaccination reminders sent: {$remindersSent}");
        Log::info("Vaccination reminders command completed. Total reminders sent: {$remindersSent}");
        
        return Command::SUCCESS;
    }
    
    /**
     * Send reminders for schedules on a specific date
     */
    protected function sendRemindersForDate(Carbon $date, int $daysUntil, int &$remindersSent)
    {
        $schedules = VaccinationSchedule::whereDate('vaccination_date', $date)
            ->whereIn('status', ['scheduled', 'active'])
            ->get();
        
        if ($schedules->isEmpty()) {
            $this->info("No schedules found for {$date->format('M d, Y')} ({$daysUntil} days from now)");
            return;
        }
        
        $this->info("Found {$schedules->count()} schedule(s) for {$date->format('M d, Y')} ({$daysUntil} days from now)");
        
        foreach ($schedules as $schedule) {
            try {
                $sent = $this->notifyParentsForSchedule($schedule, $daysUntil);
                $remindersSent += $sent;
                
                $this->info("  ✓ Sent {$sent} reminder(s) for {$schedule->barangay} schedule");
                
                Log::info("Vaccination reminders sent", [
                    'schedule_id' => $schedule->id,
                    'barangay' => $schedule->barangay,
                    'vaccination_date' => $schedule->vaccination_date->format('Y-m-d'),
                    'days_until' => $daysUntil,
                    'reminders_sent' => $sent,
                ]);
                
            } catch (\Exception $e) {
                $this->error("  ✗ Failed to send reminders for schedule {$schedule->id}: {$e->getMessage()}");
                Log::error("Failed to send vaccination reminders", [
                    'schedule_id' => $schedule->id,
                    'error' => $e->getMessage(),
                ]);
            }
        }
    }
    
    /**
     * Notify all parents for a schedule
     */
    protected function notifyParentsForSchedule(VaccinationSchedule $schedule, int $daysUntil): int
    {
        // Check if it's for RHU (Health Center) or RHU/All Barangays - notify ALL parents
        $isRHU = in_array($schedule->barangay, ['RHU (Health Center)', 'RHU/All Barangays']);
        
        // Get all patients with their parents
        $query = Patient::with('parent')->whereHas('parent');
        
        // If NOT RHU, filter by specific barangay (case-insensitive)
        if (!$isRHU) {
            $query->whereRaw('LOWER(barangay) = ?', [strtolower($schedule->barangay)]);
        }
        
        $patients = $query->get();
        $notificationsSent = 0;
        
        foreach ($patients as $patient) {
            $parent = $patient->parent;
            
            if (!$parent) {
                continue;
            }
            
            // Send reminder notification
            $parent->notify(new VaccinationReminder($schedule, $patient, $daysUntil));
            $notificationsSent++;
        }
        
        return $notificationsSent;
    }
}
