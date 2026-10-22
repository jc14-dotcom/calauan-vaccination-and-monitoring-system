<?php
/**
 * Failed Jobs Manager for Shared Hosting
 * 
 * Access via browser:
 * https://YOUR_PUBLIC_HOST/manage-failed-jobs.php?key=YOUR_SECRET_KEY
 * 
 * This script allows you to:
 * - View all failed jobs
 * - Retry individual or all failed jobs
 * - Delete old failed jobs
 * 
 * SECURITY: Requires a secret key to execute
 */

// ============================================
// Configure FAILED_JOBS_SECRET in the deployment environment.
// ============================================
$secretKey = getenv('FAILED_JOBS_SECRET') ?: '';

if ($secretKey === '') {
    http_response_code(503);
    exit('Failed jobs utility is not configured.');
}

// Verify the key
$providedKey = $_GET['key'] ?? '';

header('Content-Type: text/html; charset=utf-8');

$styles = '
    body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; background: #f5f5f5; }
    .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    h1 { color: #6a4baa; }
    h2 { color: #333; border-bottom: 2px solid #6a4baa; padding-bottom: 10px; }
    .success { color: #10b981; background: #d1fae5; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
    .error { color: #ef4444; background: #fee2e2; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
    .info { color: #3b82f6; background: #dbeafe; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
    .warning { color: #f59e0b; background: #fef3c7; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
    table { width: 100%; border-collapse: collapse; margin: 20px 0; }
    th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
    th { background: #6a4baa; color: white; }
    tr:hover { background: #f5f5f5; }
    .btn { display: inline-block; padding: 8px 16px; background: #6a4baa; color: white; text-decoration: none; border-radius: 5px; margin: 2px; font-size: 14px; border: none; cursor: pointer; }
    .btn:hover { background: #553c9a; }
    .btn-success { background: #10b981; }
    .btn-success:hover { background: #059669; }
    .btn-danger { background: #ef4444; }
    .btn-danger:hover { background: #dc2626; }
    .btn-warning { background: #f59e0b; }
    .btn-warning:hover { background: #d97706; }
    .badge { display: inline-block; padding: 4px 8px; border-radius: 4px; font-size: 12px; }
    .badge-error { background: #fee2e2; color: #ef4444; }
    .payload { max-width: 400px; overflow: hidden; text-overflow: ellipsis; white-space: nowrap; font-size: 12px; color: #666; }
    .payload:hover { white-space: normal; word-break: break-all; }
    pre { background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; overflow-x: auto; font-size: 12px; max-height: 300px; }
    .stats { display: flex; gap: 20px; margin: 20px 0; }
    .stat-card { background: linear-gradient(135deg, #6a4baa, #553c9a); color: white; padding: 20px; border-radius: 10px; flex: 1; text-align: center; }
    .stat-number { font-size: 36px; font-weight: bold; }
    .stat-label { font-size: 14px; opacity: 0.9; }
';

echo '<!DOCTYPE html>
<html>
<head>
    <title>Failed Jobs Manager</title>
    <style>' . $styles . '</style>
</head>
<body>
<div class="container">';

if ($providedKey !== $secretKey) {
    echo '<h1>🔒 Failed Jobs Manager</h1>';
    echo '<div class="error">❌ Access Denied: Invalid or missing security key.</div>';
    echo '<p>Usage: <code>manage-failed-jobs.php?key=YOUR_SECRET_KEY</code></p>';
    echo '</div></body></html>';
    exit;
}

try {
    // Bootstrap Laravel
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    $action = $_GET['action'] ?? 'list';
    $jobId = $_GET['id'] ?? null;
    
    echo '<h1>📋 Failed Jobs Manager</h1>';
    
    // Get failed jobs count
    $failedJobsCount = DB::table('failed_jobs')->count();
    $oldestJob = DB::table('failed_jobs')->orderBy('failed_at', 'asc')->first();
    $newestJob = DB::table('failed_jobs')->orderBy('failed_at', 'desc')->first();
    
    // Stats
    echo '<div class="stats">';
    echo '<div class="stat-card"><div class="stat-number">' . $failedJobsCount . '</div><div class="stat-label">Failed Jobs</div></div>';
    if ($oldestJob) {
        echo '<div class="stat-card"><div class="stat-number">' . date('M d', strtotime($oldestJob->failed_at)) . '</div><div class="stat-label">Oldest Failure</div></div>';
    }
    if ($newestJob) {
        echo '<div class="stat-card"><div class="stat-number">' . date('M d', strtotime($newestJob->failed_at)) . '</div><div class="stat-label">Latest Failure</div></div>';
    }
    echo '</div>';
    
    // Action buttons
    echo '<p>';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=list" class="btn">📋 View All Jobs</a> ';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=retry_all" class="btn btn-success" onclick="return confirm(\'Retry ALL failed jobs?\')">🔄 Retry All Jobs</a> ';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=delete_old" class="btn btn-warning" onclick="return confirm(\'Delete jobs older than 7 days?\')">🗑️ Delete Old (>7 days)</a> ';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=delete_all" class="btn btn-danger" onclick="return confirm(\'DELETE ALL failed jobs? This cannot be undone!\')">❌ Delete All</a>';
    echo '</p>';
    
    // Handle actions
    switch ($action) {
        case 'retry_all':
            echo '<h2>🔄 Retrying All Failed Jobs</h2>';
            echo '<pre>';
            $exitCode = Artisan::call('queue:retry', ['id' => 'all']);
            echo Artisan::output();
            echo '</pre>';
            if ($exitCode === 0) {
                echo '<div class="success">✅ All failed jobs have been pushed back to the queue for retry.</div>';
                echo '<div class="info">ℹ️ Jobs will be processed by the next cron run (every minute).</div>';
            } else {
                echo '<div class="error">❌ Some errors occurred during retry.</div>';
            }
            break;
            
        case 'retry_one':
            if ($jobId) {
                echo '<h2>🔄 Retrying Job #' . htmlspecialchars($jobId) . '</h2>';
                echo '<pre>';
                $exitCode = Artisan::call('queue:retry', ['id' => [$jobId]]);
                echo Artisan::output();
                echo '</pre>';
                if ($exitCode === 0) {
                    echo '<div class="success">✅ Job has been pushed back to the queue.</div>';
                } else {
                    echo '<div class="error">❌ Failed to retry job.</div>';
                }
            }
            break;
            
        case 'delete_one':
            if ($jobId) {
                DB::table('failed_jobs')->where('id', $jobId)->delete();
                echo '<div class="success">✅ Job #' . htmlspecialchars($jobId) . ' has been deleted.</div>';
            }
            break;
            
        case 'delete_old':
            $deleted = DB::table('failed_jobs')
                ->where('failed_at', '<', now()->subDays(7))
                ->delete();
            echo '<div class="success">✅ Deleted ' . $deleted . ' jobs older than 7 days.</div>';
            break;
            
        case 'delete_all':
            echo '<pre>';
            $exitCode = Artisan::call('queue:flush');
            echo Artisan::output();
            echo '</pre>';
            echo '<div class="success">✅ All failed jobs have been deleted.</div>';
            break;
            
        case 'view':
            if ($jobId) {
                $job = DB::table('failed_jobs')->where('id', $jobId)->first();
                if ($job) {
                    echo '<h2>📄 Job Details #' . $job->id . '</h2>';
                    echo '<p><a href="?key=' . urlencode($secretKey) . '&action=list" class="btn">← Back to List</a></p>';
                    echo '<table>';
                    echo '<tr><th width="150">UUID</th><td>' . $job->uuid . '</td></tr>';
                    echo '<tr><th>Connection</th><td>' . $job->connection . '</td></tr>';
                    echo '<tr><th>Queue</th><td>' . $job->queue . '</td></tr>';
                    echo '<tr><th>Failed At</th><td>' . $job->failed_at . '</td></tr>';
                    echo '</table>';
                    
                    echo '<h3>Payload</h3>';
                    $payload = json_decode($job->payload, true);
                    echo '<pre>' . htmlspecialchars(json_encode($payload, JSON_PRETTY_PRINT)) . '</pre>';
                    
                    echo '<h3>Exception</h3>';
                    echo '<pre style="color: #ef4444;">' . htmlspecialchars($job->exception) . '</pre>';
                    
                    echo '<p>';
                    echo '<a href="?key=' . urlencode($secretKey) . '&action=retry_one&id=' . $job->id . '" class="btn btn-success">🔄 Retry This Job</a> ';
                    echo '<a href="?key=' . urlencode($secretKey) . '&action=delete_one&id=' . $job->id . '" class="btn btn-danger" onclick="return confirm(\'Delete this job?\')">❌ Delete</a>';
                    echo '</p>';
                }
            }
            break;
    }
    
    // List all jobs (for list action or after other actions)
    if ($action === 'list' || $action === 'delete_old' || $action === 'retry_all' || $action === 'delete_one' || $action === 'retry_one') {
        $failedJobs = DB::table('failed_jobs')
            ->orderBy('failed_at', 'desc')
            ->limit(100)
            ->get();
        
        echo '<h2>📋 Failed Jobs List</h2>';
        
        if ($failedJobs->isEmpty()) {
            echo '<div class="success">✅ No failed jobs! All queues are healthy.</div>';
        } else {
            echo '<table>';
            echo '<tr><th>ID</th><th>Job Type</th><th>Queue</th><th>Failed At</th><th>Error</th><th>Actions</th></tr>';
            
            foreach ($failedJobs as $job) {
                $payload = json_decode($job->payload, true);
                $jobName = $payload['displayName'] ?? 'Unknown';
                $shortError = substr($job->exception, 0, 100) . '...';
                
                echo '<tr>';
                echo '<td>' . $job->id . '</td>';
                echo '<td><span class="badge badge-error">' . htmlspecialchars(class_basename($jobName)) . '</span></td>';
                echo '<td>' . $job->queue . '</td>';
                echo '<td>' . date('M d, H:i', strtotime($job->failed_at)) . '</td>';
                echo '<td class="payload" title="' . htmlspecialchars($job->exception) . '">' . htmlspecialchars($shortError) . '</td>';
                echo '<td>';
                echo '<a href="?key=' . urlencode($secretKey) . '&action=view&id=' . $job->id . '" class="btn">👁️</a> ';
                echo '<a href="?key=' . urlencode($secretKey) . '&action=retry_one&id=' . $job->id . '" class="btn btn-success">🔄</a> ';
                echo '<a href="?key=' . urlencode($secretKey) . '&action=delete_one&id=' . $job->id . '" class="btn btn-danger" onclick="return confirm(\'Delete?\')">❌</a>';
                echo '</td>';
                echo '</tr>';
            }
            
            echo '</table>';
            
            if ($failedJobsCount > 100) {
                echo '<div class="info">ℹ️ Showing first 100 of ' . $failedJobsCount . ' failed jobs.</div>';
            }
        }
    }
    
} catch (Exception $e) {
    echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
    echo '<pre>' . htmlspecialchars($e->getTraceAsString()) . '</pre>';
}

echo '<hr>';
echo '<div class="warning">⚠️ For security, consider changing the secret key regularly or deleting this file when not needed.</div>';
echo '<p><a href="/clear-cache.php?key=' . urlencode(getenv('CACHE_CLEAR_SECRET') ?: '') . '" class="btn">🧹 Go to Cache Clear Utility</a></p>';
echo '</div></body></html>';
