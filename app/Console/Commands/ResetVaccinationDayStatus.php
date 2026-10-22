<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\VaccinationSchedule;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class ResetVaccinationDayStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'vaccinations:reset-daily-status 
                            {--dry-run : Run without making changes}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Reset daily vaccination status flags and mark yesterday\'s schedules as completed';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $isDryRun = $this->option('dry-run');
        
        if ($isDryRun) {
            $this->info('🔍 DRY RUN MODE - No changes will be made');
        }
        
        $this->info('Resetting daily vaccination status...');
        
        // Use Asia/Manila timezone
        $today = Carbon::today('Asia/Manila');
        $yesterday = Carbon::yesterday('Asia/Manila');
        
        // 1. Mark yesterday's active schedules as completed
        $yesterdaySchedules = VaccinationSchedule::whereDate('vaccination_date', $yesterday)
            ->where('status', 'active')
            ->get();
        
        if ($yesterdaySchedules->count() > 0) {
            $this->info("Found {$yesterdaySchedules->count()} active schedule(s) from yesterday that need to be completed.");
            
            foreach ($yesterdaySchedules as $schedule) {
                $this->line("  - {$schedule->barangay} ({$schedule->vaccination_date->format('M d, Y')})");
                
                if (!$isDryRun) {
                    $schedule->update(['status' => 'completed']);
                }
            }
            
            $this->info("  ✓ Marked as completed");
        } else {
            $this->info("No pending active schedules from yesterday.");
        }
        
        // 2. Auto-activate today's scheduled vaccinations
        $todaySchedules = VaccinationSchedule::whereDate('vaccination_date', $today)
            ->where('status', 'scheduled')
            ->get();
        
        if ($todaySchedules->count() > 0) {
            $this->info("Found {$todaySchedules->count()} scheduled vaccination(s) for today.");
            
            foreach ($todaySchedules as $schedule) {
                $this->line("  - {$schedule->barangay}");
                
                if (!$isDryRun) {
                    $schedule->update(['status' => 'active']);
                }
            }
            
            $this->info("  ✓ Activated for today");
        } else {
            $this->info("No scheduled vaccinations for today.");
        }
        
        // 3. Clear any cached vaccination session data
        // Note: Session data is per-user, but we can clear cache-based flags if any
        if (!$isDryRun) {
            // Clear any global vaccination day cache flags
            Cache::forget('vaccination_day_active');
            Cache::forget('vaccination_day_date');
        }
        
        $this->newLine();
        $this->info("=== Daily Reset Complete ===");
        $this->info("Date: {$today->format('F j, Y')}");
        $this->info("Yesterday's schedules completed: {$yesterdaySchedules->count()}");
        $this->info("Today's schedules activated: {$todaySchedules->count()}");
        
        Log::info("Daily vaccination status reset completed", [
            'date' => $today->toDateString(),
            'yesterday_completed' => $yesterdaySchedules->count(),
            'today_activated' => $todaySchedules->count(),
        ]);
        
        return Command::SUCCESS;
    }
}
