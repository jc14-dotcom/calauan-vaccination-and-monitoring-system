<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\BackupSetting;
use App\Http\Controllers\BackupController;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;

class RunScheduledBackup extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'backup:run-scheduled';

    /**
     * The console command description.
     */
    protected $description = 'Run scheduled backup based on backup settings';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $settings = BackupSetting::getInstance();

        if (!$settings->enabled) {
            $this->info('Automatic backups are disabled.');
            return Command::SUCCESS;
        }

        $now = Carbon::now();
        $shouldRun = false;

        // Check if backup should run based on schedule
        if ($settings->next_backup_at === null) {
            // First run - calculate next backup time
            $settings->next_backup_at = $settings->calculateNextBackupTime();
            $settings->save();
            $this->info('Next backup scheduled for: ' . $settings->next_backup_at->format('F d, Y h:i A'));
            return Command::SUCCESS;
        }

        // Check if it's time to run
        if ($now->gte($settings->next_backup_at)) {
            $shouldRun = true;
        }

        if (!$shouldRun) {
            $this->info('Not yet time for backup. Next backup: ' . $settings->next_backup_at->format('F d, Y h:i A'));
            return Command::SUCCESS;
        }

        $this->info('Starting scheduled backup...');

        try {
            // Create backup using the controller's logic
            $backupController = app(BackupController::class);
            $result = $backupController->createScheduledBackup();

            if ($result['success']) {
                // Update last backup time and calculate next
                $settings->last_backup_at = $now;
                $settings->next_backup_at = $settings->calculateNextBackupTime();
                $settings->save();

                $this->info('Backup created successfully: ' . $result['backup']['name']);
                $this->info('Next backup scheduled for: ' . $settings->next_backup_at->format('F d, Y h:i A'));

                Log::info('Scheduled backup completed successfully', [
                    'backup_name' => $result['backup']['name'],
                    'next_backup' => $settings->next_backup_at,
                ]);
            } else {
                $this->error('Backup failed: ' . $result['message']);
                Log::error('Scheduled backup failed', ['error' => $result['message']]);
            }
        } catch (\Exception $e) {
            $this->error('Backup failed with exception: ' . $e->getMessage());
            Log::error('Scheduled backup exception', [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return Command::FAILURE;
        }

        return Command::SUCCESS;
    }
}
