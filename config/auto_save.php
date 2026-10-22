<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Auto-Save Configuration
    |--------------------------------------------------------------------------
    |
    | Configure automatic report saving behavior
    |
    */

    'enabled' => env('AUTO_SAVE_REPORTS_ENABLED', true),
    
    'monthly' => [
        'enabled' => env('AUTO_SAVE_MONTHLY_ENABLED', true),
        'schedule' => '0 1 1 * *', // First day of month at 1:00 AM
        'timezone' => 'Asia/Manila',
    ],
    
    'quarterly' => [
        'enabled' => env('AUTO_SAVE_QUARTERLY_ENABLED', true),
        'schedule' => '0 2 1 1,4,7,10 *', // Jan, Apr, Jul, Oct at 2:00 AM
        'timezone' => 'Asia/Manila',
    ],
    
    'notifications' => [
        'enabled' => env('AUTO_SAVE_NOTIFICATIONS_ENABLED', false),
        'email' => env('AUTO_SAVE_NOTIFICATION_EMAIL', 'admin@example.com'),
    ],
    
    'retention' => [
        'keep_versions' => env('AUTO_SAVE_KEEP_VERSIONS', 5), // Keep last 5 versions
        'auto_delete_old' => env('AUTO_SAVE_AUTO_DELETE_OLD', false),
    ],
];
