<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Two-Factor Authentication Configuration
    |--------------------------------------------------------------------------
    |
    | This file contains the configuration options for the Laravel 2FA package.
    | You can customize these settings according to your application needs.
    |
    */

    'enabled' => env('ENABLE_2FA', false),
    'redirect_after' => '/home',

    /*
    |--------------------------------------------------------------------------
    | Redirect URLs
    |--------------------------------------------------------------------------
    |
    | Configure where users should be redirected after various 2FA actions.
    |
    */
    'redirect_after_setup' => env('2FA_REDIRECT_AFTER_SETUP', '/home'),
    'redirect_after_verify' => env('2FA_REDIRECT_AFTER_VERIFY', '/home'),

    /*
    |--------------------------------------------------------------------------
    | QR Code Settings
    |--------------------------------------------------------------------------
    |
    | Configure QR code generation settings.
    |
    */
    'qr_code' => [
        'size' => env('2FA_QR_SIZE', 200),
        'format' => env('2FA_QR_FORMAT', 'png'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Google2FA Settings
    |--------------------------------------------------------------------------
    |
    | Configure Google2FA library settings.
    |
    */
    'google2fa' => [
        'window' => env('2FA_WINDOW', 4), // Number of time steps to allow for clock skew
        'length' => env('2FA_LENGTH', 6), // Length of the generated code
        'time_step' => env('2FA_TIME_STEP', 30), // Time step in seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Session Settings
    |--------------------------------------------------------------------------
    |
    | Configure session-related settings for 2FA.
    |
    */
    'session' => [
        'key' => env('2FA_SESSION_KEY', '2fa_verified'),
        'lifetime' => env('2FA_SESSION_LIFETIME', 120), // Minutes
    ],

    /*
    |--------------------------------------------------------------------------
    | User Model Settings
    |--------------------------------------------------------------------------
    |
    | Configure the user model fields used for 2FA.
    |
    */
    'user_model' => [
        'secret_field' => env('2FA_SECRET_FIELD', 'two_factor_secret'),
        'enabled_field' => env('2FA_ENABLED_FIELD', 'two_factor_enabled'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Middleware Settings
    |--------------------------------------------------------------------------
    |
    | Configure middleware behavior.
    |
    */
    'middleware' => [
        'except' => [
            '2fa.*',
            'login',
            'logout',
            'password.*',
        ],
    ],
];
