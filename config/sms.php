<?php

return [

    /*
    |--------------------------------------------------------------------------
    | SMS Service Configuration
    |--------------------------------------------------------------------------
    |
    | This file is for configuring SMS notifications via Semaphore gateway.
    | SMS is DISABLED by default to avoid costs. Enable it only when needed.
    |
    */

    // SMS service enabled/disabled (default: false)
    'enabled' => env('SMS_ENABLED', false),

    // Semaphore API Configuration
    'semaphore' => [
        'api_key' => env('SEMAPHORE_API_KEY', ''),
        'sender_name' => env('SEMAPHORE_SENDER_NAME', 'RHUCALAUAN'),
        'api_url' => env('SEMAPHORE_API_URL', 'https://api.semaphore.co/api/v4/messages'),
    ],

    /*
    |--------------------------------------------------------------------------
    | SMS Notification Types
    |--------------------------------------------------------------------------
    |
    | Configure which notification types should trigger SMS.
    | All are disabled by default.
    |
    */

    'triggers' => [
        'vaccination_schedule_created' => env('SMS_TRIGGER_SCHEDULE_CREATED', false),
        'vaccination_schedule_cancelled' => env('SMS_TRIGGER_SCHEDULE_CANCELLED', false),
        'vaccination_reminder' => env('SMS_TRIGGER_REMINDER', false),
        'low_stock_alert' => env('SMS_TRIGGER_LOW_STOCK', false),
        'feedback_request' => env('SMS_TRIGGER_FEEDBACK', false),
    ],

    /*
    |--------------------------------------------------------------------------
    | Cost Tracking
    |--------------------------------------------------------------------------
    |
    | Track SMS costs for budgeting purposes
    | Average cost per SMS: ₱0.50-0.85 (we use ₱0.65 as average)
    |
    */

    'cost_per_sms' => env('SMS_COST_PER_SMS', 0.65),
    'cost_currency' => 'PHP',

];
