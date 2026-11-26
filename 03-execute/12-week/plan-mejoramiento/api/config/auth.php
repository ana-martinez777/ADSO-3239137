<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Default authentication guard and the provider used to reset passwords.
    |
    */

    // JWT is the default authentication mechanism.
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Mechanisms available to authenticate requests.
    | The API uses our custom JWT-based guard.
    |
    */

    'guards' => [
        'api' => [
            // Uses the JWT mechanism registered in AppServiceProvider.
            'driver' => 'jwt',

            // Users are loaded through the Eloquent provider.
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | How Laravel loads the users from the database.
    |
    */

    'providers' => [
        'users' => [
            // Uses Eloquent to query user records.
            'driver' => 'eloquent',

            // Model that represents the users.
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Password reset settings.
    |
    */

    'passwords' => [
        'users' => [
            // Uses the user provider defined above.
            'provider' => 'users',

            // Table that stores the reset tokens.
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),

            // Validity of the reset token, in minutes.
            'expire' => 60,

            // Waiting time between reset requests.
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Maximum validity of a password confirmation.
    |
    */

    // Timeout in seconds.
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
