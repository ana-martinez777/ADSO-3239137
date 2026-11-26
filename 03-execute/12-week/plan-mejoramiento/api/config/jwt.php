<?php

return [
    'secret' => env('JWT_SECRET'), // reads the secret key from .env
    'algorithm' => 'HS256', // HS256 is used to sign and verify the tokens.
    'expiration' => 480, // sets 8 hours.
];