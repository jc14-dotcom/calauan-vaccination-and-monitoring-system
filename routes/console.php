<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote')->hourly();

// =====================================================================
// CORE SYSTEM TASKS (Always run)
// =====================================================================

// Run scheduled database backup - checks daily if it's time for a backup
Schedule::command('backup:run-scheduled')
    ->dailyAt('02:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

// Send vaccination reminders daily at 6:00 AM
Schedule::command('notifications:send-vaccination-reminders')
    ->dailyAt('06:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

// =====================================================================
// VACCINATION DAY STATUS MANAGEMENT
// =====================================================================

// Process missed vaccinations at 6:00 PM Philippine Time
// Marks patients who didn't show up for today's vaccination as "missed"
// and sends notifications to their parents
Schedule::command('vaccinations:process-missed')
    ->dailyAt('18:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

// Reset daily vaccination status at 5:00 AM Philippine Time
// Completes yesterday's schedules and activates today's schedules
Schedule::command('vaccinations:reset-daily-status')
    ->dailyAt('05:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

// =====================================================================
// AUTO-SAVE REPORTS (Conditional)
// =====================================================================

// Auto-save monthly report on the 1st day of each month at 1:00 AM
if (config('auto_save.enabled', true) && config('auto_save.monthly.enabled', true)) {
    Schedule::command('reports:auto-save-monthly')
        ->monthlyOn(1, '01:00')
        ->timezone(config('auto_save.monthly.timezone', 'Asia/Manila'))
        ->withoutOverlapping();
}

// Auto-save quarterly report on the first day after each quarter ends
if (config('auto_save.enabled', true) && config('auto_save.quarterly.enabled', true)) {
    Schedule::command('reports:auto-save-quarterly')
        ->cron('0 2 1 1,4,7,10 *')
        ->timezone(config('auto_save.quarterly.timezone', 'Asia/Manila'))
        ->withoutOverlapping();
}

// =====================================================================
// ACTIVITY LOG CLEANUP
// =====================================================================

// Clean up old activity logs on the 1st of each month at 3:00 AM
// Removes logs older than 365 days (configured in config/activitylog.php)
Schedule::command('activitylog:clean')
    ->monthlyOn(1, '03:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();

// =====================================================================
// FAILED JOBS MANAGEMENT
// =====================================================================

// Retry failed jobs automatically every 6 hours
// This gives jobs multiple chances to succeed (e.g., if SMTP was temporarily down)
Schedule::command('queue:retry', ['id' => 'all'])
    ->everySixHours()
    ->timezone('Asia/Manila')
    ->withoutOverlapping()
    ->onSuccess(function () {
        \Log::info('Failed jobs retry completed successfully');
    })
    ->onFailure(function () {
        \Log::warning('Failed jobs retry encountered issues');
    });

// Clean up old failed jobs (older than 30 days) weekly on Sunday at 4:00 AM
// Prevents the failed_jobs table from growing indefinitely
Schedule::call(function () {
    $deleted = \DB::table('failed_jobs')
        ->where('failed_at', '<', now()->subDays(30))
        ->delete();
    
    if ($deleted > 0) {
        \Log::info("Cleaned up {$deleted} old failed jobs (older than 30 days)");
    }
})
    ->name('cleanup-old-failed-jobs')
    ->weeklyOn(0, '04:00')
    ->timezone('Asia/Manila')
    ->withoutOverlapping();
