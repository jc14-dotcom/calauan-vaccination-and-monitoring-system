<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VaccinationSchedule;
use App\Models\Patient;
use App\Models\VaccinationTransaction;
use App\Notifications\VaccinationMissed;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class ProcessMissedVaccinations extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vaccinations:process-missed 
                            {--dry-run : Run without sending notifications or updating database}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Process missed vaccinations for today\'s completed schedules and send notifications to parents';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('Processing missed vaccinations for today...');
        
        // Use Asia/Manila timezone
        $today = Carbon::today('Asia/Manila');
        $now = Carbon::now('Asia/Manila');
        
        // Only process after 6 PM
        if ($now->hour < 18 && !$isDryRun) {
            $this->info("Current time is {$now->format('H:i')}. Skipping - this command should run after 6 PM.");
            return Command::SUCCESS;
        }
        
        // Find today's schedules that are active or completed
        $todaySchedules = VaccinationSchedule::whereDate('vaccination_date', $today)
            ->whereIn('status', ['active', 'completed'])
            ->get();
        
        if ($todaySchedules->isEmpty()) {
            $this->info('No active vaccination schedules for today.');
            return Command::SUCCESS;
        }
        
        $this->info("Found {$todaySchedules->count()} schedule(s) for today.");
        
        $totalMissed = 0;
        $totalNotified = 0;
        
        foreach ($todaySchedules as $schedule) {
            $this->info("\nProcessing schedule for {$schedule->barangay}...");
            
            // Get patients for this barangay who have incomplete vaccines
            $query = Patient::with(['parent', 'vaccines']);
            
            if ($schedule->barangay !== 'RHU/All Barangays' && $schedule->barangay !== 'RHU (Health Center)') {
                // Case-insensitive barangay matching
                $query->whereRaw('LOWER(barangay) = ?', [strtolower($schedule->barangay)]);
            }
            
            $patients = $query->get();
            
            // Filter patients who have incomplete vaccines
            $incompletePatients = $patients->filter(function($patient) {
                return $patient->hasIncompleteVaccines();
            });
            
            $this->info("  Found {$incompletePatients->count()} patients with incomplete vaccines");
            
            foreach ($incompletePatients as $patient) {
                // Check if patient was vaccinated today
                $wasVaccinatedToday = VaccinationTransaction::where('patient_id', $patient->id)
                    ->whereDate('created_at', $today)
                    ->exists();
                
                if ($wasVaccinatedToday) {
                    continue; // Skip - they got vaccinated
                }
                
                // This patient missed their vaccination
                $totalMissed++;
                
                $this->line("    ❌ {$patient->name} - Missed vaccination");
                
                // Send notification to parent
                if ($patient->parent && !$isDryRun) {
                    try {
                        $patient->parent->notify(new VaccinationMissed($schedule));
                        $totalNotified++;
                        $this->info("      ✉ Notification sent to parent");
                    } catch (\Exception $e) {
                        $this->error("      ✗ Failed to send notification: {$e->getMessage()}");
                        Log::error("Failed to send missed vaccination notification", [
                            'patient_id' => $patient->id,
                            'parent_id' => $patient->parent_id,
                            'error' => $e->getMessage(),
                        ]);
                    }
                } elseif (!$patient->parent) {
                    $this->warn("      ⚠ No parent account linked");
                }
            }
            
            // Mark schedule as completed if not already
            if (!$isDryRun && $schedule->status !== 'completed') {
                $schedule->update(['status' => 'completed']);
                $this->info("  ✓ Schedule marked as completed");
            }
        }
        
        $this->newLine();
        $this->info("=== Summary ===");
        $this->info("Total missed: {$totalMissed}");
        $this->info("Notifications sent: {$totalNotified}");
        
        Log::info("Processed missed vaccinations", [
            'date' => $today->toDateString(),
            'schedules_processed' => $todaySchedules->count(),
            'total_missed' => $totalMissed,
            'notifications_sent' => $totalNotified,
        ]);
        
        return Command::SUCCESS;
    }
}
