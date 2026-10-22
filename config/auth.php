<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | This option defines the default authentication "guard" and password
    | reset "broker" for your application. You may change these values
    | as required, but they're a perfect start for most applications.
    |
    */

    'defaults' => [
    'guard' => 'parents',  // Change this to 'parents'
    'passwords' => 'parents',  // Update this to match your 'parents' password broker
],


    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Next, you may define every authentication guard for your application.
    | Of course, a great default configuration has been defined for you
    | which utilizes session storage plus the Eloquent user provider.
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | Supported: "session"
    |
    */

    'guards' => [
    'web' => [
        'driver' => 'session',
        'provider' => 'users',  // Assuming this is for regular users
    ],
    'parents' => [
        'driver' => 'session',
        'provider' => 'Parents',  // Ensure this is the right provider for Parents
    ],
    'health_worker' => [
        'driver' => 'session',
        'provider' => 'health_workers',
    ],
],





    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | All authentication guards have a user provider, which defines how the
    | users are actually retrieved out of your database or other storage
    | system used by the application. Typically, Eloquent is utilized.
    |
    | If you have multiple user tables or models you may configure multiple
    | providers to represent the model / table. These providers may then
    | be assigned to any extra authentication guards you have defined.
    |
    | Supported: "database", "eloquent"
    |
    */

    'providers' => [
    'Parents' => [
        'driver' => 'eloquent',
        'model' => App\Models\Parents::class,  // This should be the correct model
    ],


    'health_workers' => [
        'driver' => 'eloquent',
           'model' => App\Models\HealthWorker::class,
        ],
        'users' => [
        'driver' => 'eloquent',
        'model' => App\Models\User::class,
    ],
        // 'users' => [
        //     'driver' => 'database',
        //     'table' => 'users',
        // ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | These configuration options specify the behavior of Laravel's password
    | reset functionality, including the table utilized for token storage
    | and the user provider that is invoked to actually retrieve users.
    |
    | The expiry time is the number of minutes that each reset token will be
    | considered valid. This security feature keeps tokens short-lived so
    | they have less time to be guessed. You may change this as needed.
    |
    | The throttle setting is the number of seconds a user must wait before
    | generating more password reset tokens. This prevents the user from
    | quickly generating a very large amount of password reset tokens.
    |
    */
    'passwords' => [
    'parents' => [ // Rename this section to 'parents'
        'provider' => 'Parents', // Change to 'Parents' provider
        'table' => 'password_reset_tokens', // Keep this as the table name for reset tokens
        'expire' => 60,
        'throttle' => 60,
    ],
    'health_workers' => [ // Add this section for health workers
        'provider' => 'health_workers', // Use the 'health_workers' provider
        'table' => 'password_reset_tokens', // Keep this as the table name for reset tokens
        'expire' => 60,
        'throttle' => 60,
    ],
],

   // 'passwords' => [
   //     'users' => [
   //         'provider' => 'users',
   //         'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),
   //         'expire' => 60,
   //         'throttle' => 60,
   //    ],
   // ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Here you may define the amount of seconds before a password confirmation
    | window expires and users are asked to re-enter their password via the
    | confirmation screen. By default, the timeout lasts for three hours.
    |
    */

    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
