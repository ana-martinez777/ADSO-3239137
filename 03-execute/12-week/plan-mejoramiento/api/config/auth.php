<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Authentication Defaults
    |--------------------------------------------------------------------------
    |
    | Define el mecanismo de autenticación predeterminado y el proveedor
    | utilizado para la recuperación de contraseñas.
    |
    */

    // Establece JWT como mecanismo predeterminado de autenticación.
    'defaults' => [
        'guard' => env('AUTH_GUARD', 'api'),
        'passwords' => env('AUTH_PASSWORD_BROKER', 'users'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Authentication Guards
    |--------------------------------------------------------------------------
    |
    | Define los mecanismos disponibles para autenticar las solicitudes.
    | La API utiliza nuestro guard personalizado basado en JWT.
    |
    */

    'guards' => [
        'api' => [
            // Utiliza el mecanismo JWT registrado en AppServiceProvider.
            'driver' => 'jwt',

            // Obtiene los usuarios mediante el proveedor Eloquent.
            'provider' => 'users',
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | User Providers
    |--------------------------------------------------------------------------
    |
    | Define cómo Laravel obtiene los usuarios desde la base de datos.
    |
    */

    'providers' => [
        'users' => [
            // Utiliza Eloquent para consultar los registros de usuarios.
            'driver' => 'eloquent',

            // Indica el modelo que representa a los usuarios.
            'model' => env('AUTH_MODEL', App\Models\User::class),
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Resetting Passwords
    |--------------------------------------------------------------------------
    |
    | Configuración de recuperación de contraseñas.
    |
    */

    'passwords' => [
        'users' => [
            // Utiliza el proveedor de usuarios definido anteriormente.
            'provider' => 'users',

            // Define la tabla donde se almacenan los tokens de recuperación.
            'table' => env('AUTH_PASSWORD_RESET_TOKEN_TABLE', 'password_reset_tokens'),

            // Establece la vigencia del token de recuperación en minutos.
            'expire' => 60,

            // Establece el tiempo de espera entre solicitudes de recuperación.
            'throttle' => 60,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | Password Confirmation Timeout
    |--------------------------------------------------------------------------
    |
    | Tiempo máximo de validez de una confirmación de contraseña.
    |
    */

    // Define el tiempo de espera en segundos.
    'password_timeout' => env('AUTH_PASSWORD_TIMEOUT', 10800),

];
