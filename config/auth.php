<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    */

    'defaults' => [
        'guard' => 'web', // admin / default
        'passwords' => 'users',
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    */

    'guards' => [

        // 🔹 Default Laravel guard (admin / backend)
        'web' => [
            'driver' => 'session',
            'provider' => 'users',
        ],

        // 🔹 Frontend users (registrations table)
        'frontend' => [
            'driver' => 'session',
            'provider' => 'registrations',
        ],

        // 🔹 Delivery riders (delivery_men table)
        'rider' => [
            'driver' => 'session',
            'provider' => 'riders',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    */

    'providers' => [

        // 🔹 Default Laravel users (admin)
        'users' => [
            'driver' => 'eloquent',
            'model' => App\Models\User::class,
        ],

        // 🔹 Frontend users provider
        'registrations' => [
            'driver' => 'eloquent',
            'model' => App\Models\Registration::class,
        ],

        // 🔹 Delivery riders provider
        'riders' => [
            'driver' => 'eloquent',
            'model' => App\Models\DeliveryMan::class,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    */

    'passwords' => [

        // admin users
        'users' => [
            'provider' => 'users',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],

        // frontend users (optional – future use)
        'registrations' => [
            'provider' => 'registrations',
            'table' => 'password_reset_tokens',
            'expire' => 60,
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    */

    'password_timeout' => 10800,
];
