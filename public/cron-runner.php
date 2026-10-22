<?php
/**
 * Cron Runner for Laravel Scheduler and Queue Worker
 * 
 * This file can be executed by a hosting provider's cron every minute.
 * It runs both the Laravel scheduler AND processes queued jobs.
 * 
 * OPTIMIZED FOR MAXIMUM THROUGHPUT:
 * - Processes up to 100 jobs per run
 * - 55 second max time (leaves 5s buffer before next cron)
 * - Minimal sleep between jobs for faster processing
 * 
 * Cron Command:
 * Replace the PHP executable and application path with your deployment values.
 */

// Security: Only allow CLI execution (not web browser access)
if (php_sapi_name() !== 'cli') {
    http_response_code(403);
    die('Access Denied');
}

// Increase limits for CLI execution
set_time_limit(60);
ini_set('memory_limit', '256M');

date_default_timezone_set('Asia/Manila');
$startTime = microtime(true);
$timestamp = date('Y-m-d H:i:s');
$logFile = __DIR__ . '/cron-execution-log.txt';

// Limit log file size (keep last 500 lines approximately)
if (file_exists($logFile) && filesize($logFile) > 50000) {
    $lines = file($logFile);
    $lines = array_slice($lines, -300);
    file_put_contents($logFile, implode('', $lines));
}

file_put_contents($logFile, "[{$timestamp}] === Cron Started ===\n", FILE_APPEND);

try {
    // Bootstrap Laravel
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap(); // Bootstrap the application to use facades
    
    // 1. Run the scheduler first (for scheduled tasks like 6PM notifications)
    $scheduleStatus = $kernel->call('schedule:run');
    file_put_contents($logFile, "[{$timestamp}] Scheduler: Status {$scheduleStatus}\n", FILE_APPEND);
    
    // 2. Retry failed jobs every hour (at minute 0)
    // This gives failed jobs another chance if the issue was temporary (e.g., SMTP down)
    $currentMinute = (int) date('i');
    if ($currentMinute === 0) {
        try {
            $failedCount = \Illuminate\Support\Facades\DB::table('failed_jobs')->count();
            if ($failedCount > 0) {
                file_put_contents($logFile, "[{$timestamp}] Retrying {$failedCount} failed jobs...\n", FILE_APPEND);
                $retryStatus = $kernel->call('queue:retry', ['id' => 'all']);
                file_put_contents($logFile, "[{$timestamp}] Failed jobs retry: Status {$retryStatus}\n", FILE_APPEND);
            }
        } catch (Exception $retryError) {
            file_put_contents($logFile, "[{$timestamp}] Failed jobs retry error: " . $retryError->getMessage() . "\n", FILE_APPEND);
        }
    }
    
    // 3. Process queued jobs with MAXIMUM SPEED
    $queueStatus = $kernel->call('queue:work', [
        '--stop-when-empty' => true,   // Stop when no more jobs
        '--max-jobs' => 100,           // Process up to 100 jobs per run
        '--max-time' => 55,            // Max 55 seconds (5s buffer before next cron)
        '--tries' => 3,                // Retry failed jobs 3 times
        '--sleep' => 0,                // NO sleep between jobs (fastest)
        '--memory' => 128,             // Memory limit in MB
    ]);
    
    $endTime = microtime(true);
    $duration = round($endTime - $startTime, 2);
    
    file_put_contents($logFile, "[{$timestamp}] Queue Worker: Status {$queueStatus} | Duration: {$duration}s\n", FILE_APPEND);
    
} catch (Exception $e) {
    file_put_contents($logFile, "[{$timestamp}] ERROR: " . $e->getMessage() . "\n", FILE_APPEND);
}

$endTime = microtime(true);
$duration = round($endTime - $startTime, 2);
file_put_contents($logFile, "[{$timestamp}] === Cron Finished ({$duration}s) ===\n", FILE_APPEND);
