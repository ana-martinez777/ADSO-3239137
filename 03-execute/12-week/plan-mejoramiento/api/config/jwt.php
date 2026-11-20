<?php

return [
    'secret' => env('JWT_SECRET'), // obtiene la clave privada desde .env
    'algorithm' => 'HS256', // define HS256 para firmar y verificar los tokens.
    'expiration' => 480, // establece las 8 horas.
];