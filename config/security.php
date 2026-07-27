<?php

return [

    /*
    |--------------------------------------------------------------------------
    | One-time passcodes
    |--------------------------------------------------------------------------
    |
    | Every knob the OTP flows use. Kept in config rather than as class
    | constants so the expiry and throttles can be tuned per environment
    | without a code change.
    |
    */

    'otp' => [
        'length'          => (int) env('OTP_LENGTH', 6),
        'expires_minutes' => (int) env('OTP_EXPIRES_MINUTES', 10),

        // How many wrong guesses a single code tolerates before it is burned.
        'max_attempts'    => (int) env('OTP_MAX_ATTEMPTS', 5),

        // How often a new code may be requested for the same address.
        'resend_seconds'  => (int) env('OTP_RESEND_SECONDS', 60),

        // Requests per window, per email+IP, for the "send me a code" endpoints.
        'throttle' => [
            'max_per_hour' => (int) env('OTP_MAX_PER_HOUR', 6),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Admin password reset links
    |--------------------------------------------------------------------------
    |
    | A superadmin approving a password-assistance request mints a signed,
    | single-use link. Plain-text passwords are never emailed.
    |
    */

    'password_reset' => [
        'link_expires_minutes' => (int) env('ADMIN_RESET_LINK_MINUTES', 60),
        'min_password_length'  => (int) env('MIN_PASSWORD_LENGTH', 8),
    ],

    /*
    |--------------------------------------------------------------------------
    | Login throttling
    |--------------------------------------------------------------------------
    */

    'login' => [
        'max_attempts'   => (int) env('LOGIN_MAX_ATTEMPTS', 5),
        'decay_seconds'  => (int) env('LOGIN_DECAY_SECONDS', 300),
    ],

    /*
    |--------------------------------------------------------------------------
    | Account help requests
    |--------------------------------------------------------------------------
    */

    'account_requests' => [
        // A second identical request inside this window is refused.
        'cooldown_minutes' => (int) env('ACCOUNT_REQUEST_COOLDOWN', 15),
    ],

    /*
    |--------------------------------------------------------------------------
    | Live support chat
    |--------------------------------------------------------------------------
    |
    | The widget polls rather than holding a socket open, so the intervals here
    | are the real cost knob: every open widget is one query per interval.
    |
    */

    'chat' => [
        'max_length' => (int) env('CHAT_MAX_LENGTH', 2000),

        // Messages one sender may post per minute.
        'rate_per_minute' => (int) env('CHAT_RATE_PER_MINUTE', 20),

        // How many lines the widget and the admin pane load up front.
        'history_limit' => (int) env('CHAT_HISTORY_LIMIT', 50),

        'poll' => [
            // While the panel is open and the tab is focused.
            'active_ms' => (int) env('CHAT_POLL_ACTIVE_MS', 3000),

            // Panel shut — just keeping the unread badge honest.
            'idle_ms'   => (int) env('CHAT_POLL_IDLE_MS', 20000),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Audit trail
    |--------------------------------------------------------------------------
    */

    'audit' => [
        // Attributes never written to the trail, whatever model they are on.
        'never_log' => [
            'password', 'remember_token', 'created_at', 'updated_at',
            'email_verified_at', 'otp', 'otp_expires_at',
            'stripe_session_id', 'code_hash', 'token_hash',
        ],

        // Longest single value kept in old_values / new_values.
        'value_limit' => 200,
    ],

];
