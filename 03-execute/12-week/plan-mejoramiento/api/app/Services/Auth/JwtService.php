<?php

namespace App\Services\Auth;

use App\Models\User;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    /**
     * Genera un token JWT para el usuario autenticado.
     */
    public function generate(User $user): string
    {
        $now = time();
        $expiration = $now + (config('jwt.expiration') * 60);

        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'iat' => $now,
            'exp' => $expiration,
        ];

        return JWT::encode(
            $payload,
            config('jwt.secret'),
            config('jwt.algorithm')
        );
    }

    /**
     * Verifica la firma y la expiración del token.
     */
    public function validate(string $token): object
    {
        return JWT::decode(
            $token,
            new Key(
                config('jwt.secret'),
                config('jwt.algorithm')
            )
        );
    }
}
