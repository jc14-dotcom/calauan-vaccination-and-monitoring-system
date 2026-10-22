<?php
/**
 * Cache Clear Utility for Shared Hosting
 * 
 * Access via browser:
 * https://YOUR_PUBLIC_HOST/clear-cache.php?key=YOUR_SECRET_KEY
 * 
 * This script clears Laravel's config, cache, view, and route caches
 * without needing terminal access.
 * 
 * SECURITY: Requires a secret key to execute
 */

// ============================================
// Configure CACHE_CLEAR_SECRET in the deployment environment.
// ============================================
$secretKey = getenv('CACHE_CLEAR_SECRET') ?: '';

if ($secretKey === '') {
    http_response_code(503);
    exit('Cache clear utility is not configured.');
}

// Verify the key
$providedKey = $_GET['key'] ?? '';

header('Content-Type: text/html; charset=utf-8');

echo '<!DOCTYPE html>
<html>
<head>
    <title>Cache Clear Utility</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 50px auto; padding: 20px; background: #f5f5f5; }
        .container { background: white; padding: 30px; border-radius: 10px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #6a4baa; }
        .success { color: #10b981; background: #d1fae5; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        .error { color: #ef4444; background: #fee2e2; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        .info { color: #3b82f6; background: #dbeafe; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        .warning { color: #f59e0b; background: #fef3c7; padding: 10px 15px; border-radius: 5px; margin: 10px 0; }
        pre { background: #1f2937; color: #10b981; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .btn { display: inline-block; padding: 10px 20px; background: #6a4baa; color: white; text-decoration: none; border-radius: 5px; margin: 5px; }
        .btn:hover { background: #553c9a; }
        .btn-danger { background: #ef4444; }
        .btn-danger:hover { background: #dc2626; }
    </style>
</head>
<body>
<div class="container">';

if ($providedKey !== $secretKey) {
    echo '<h1>🔒 Cache Clear Utility</h1>';
    echo '<div class="error">❌ Access Denied: Invalid or missing security key.</div>';
    echo '<p>Usage: <code>clear-cache.php?key=YOUR_SECRET_KEY</code></p>';
    echo '</div></body></html>';
    exit;
}

echo '<h1>🧹 Laravel Cache Clear Utility</h1>';

$action = $_GET['action'] ?? 'show';

if ($action === 'show') {
    echo '<div class="info">ℹ️ Select what you want to clear:</div>';
    echo '<p>';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=config" class="btn">Clear Config Cache</a> ';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=cache" class="btn">Clear Application Cache</a> ';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=view" class="btn">Clear View Cache</a> ';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=route" class="btn">Clear Route Cache</a> ';
    echo '<a href="?key=' . urlencode($secretKey) . '&action=all" class="btn btn-danger">Clear ALL Caches</a>';
    echo '</p>';
    echo '<div class="warning">⚠️ After clearing config cache, your .env changes will take effect immediately.</div>';
    echo '</div></body></html>';
    exit;
}

try {
    // Bootstrap Laravel
    require __DIR__ . '/../vendor/autoload.php';
    $app = require_once __DIR__ . '/../bootstrap/app.php';
    $kernel = $app->make(Illuminate\Contracts\Console\Kernel::class);
    $kernel->bootstrap();
    
    echo '<pre>';
    
    $commands = [];
    
    switch ($action) {
        case 'config':
            $commands = ['config:clear'];
            break;
        case 'cache':
            $commands = ['cache:clear'];
            break;
        case 'view':
            $commands = ['view:clear'];
            break;
        case 'route':
            $commands = ['route:clear'];
            break;
        case 'all':
            $commands = ['config:clear', 'cache:clear', 'view:clear', 'route:clear'];
            break;
        default:
            echo "Unknown action: {$action}";
            exit;
    }
    
    foreach ($commands as $command) {
        echo "Running: php artisan {$command}\n";
        $exitCode = Artisan::call($command);
        echo Artisan::output();
        echo "Exit code: {$exitCode}\n";
        echo "---\n";
    }
    
    echo '</pre>';
    
    echo '<div class="success">✅ Cache cleared successfully!</div>';
    
    // Show current mail config to verify
    if ($action === 'config' || $action === 'all') {
        echo '<div class="info">';
        echo '<strong>Current Mail Configuration (after cache clear):</strong><br>';
        echo 'MAIL_HOST: ' . config('mail.mailers.smtp.host') . '<br>';
        echo 'MAIL_PORT: ' . config('mail.mailers.smtp.port') . '<br>';
        echo 'MAIL_USERNAME: ' . config('mail.mailers.smtp.username') . '<br>';
        echo 'MAIL_PASSWORD: ' . substr(config('mail.mailers.smtp.password'), 0, 4) . '****' . substr(config('mail.mailers.smtp.password'), -4) . '<br>';
        echo '</div>';
    }
    
} catch (Exception $e) {
    echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
}

echo '<p><a href="?key=' . urlencode($secretKey) . '" class="btn">← Back to Menu</a></p>';
echo '<div class="warning">⚠️ For security, consider deleting this file after use or changing the secret key regularly.</div>';
echo '</div></body></html>';
