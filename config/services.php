<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'fcm' => [
        'credentials_path' => env('FCM_CREDENTIALS_PATH', 'firebase/infantvax-firebase-adminsdk.json'),
        'project_id' => env('FCM_PROJECT_ID', 'infant-vaccination-syste-508e4'),
        'sender_id' => env('FCM_SENDER_ID', '182620664136'),
        'api_key' => env('FCM_API_KEY'),
        'app_id' => env('FCM_APP_ID'),
        'auth_domain' => env('FCM_AUTH_DOMAIN'),
        'storage_bucket' => env('FCM_STORAGE_BUCKET'),
        'web_push_certificate' => env('FCM_WEB_PUSH_CERTIFICATE'),
    ],

];
