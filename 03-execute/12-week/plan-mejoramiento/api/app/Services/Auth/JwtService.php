<?php
namespace App\Services\Auth;

use App\Application\Ports\TokenIssuer;
use App\Domain\Users\UserAccount;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService implements TokenIssuer
{
    /** Genera un token JWT para el usuario autenticado. */
    public function generate(UserAccount $user): string
    {
        // Calcula los tiempos de emisión y expiración del token.
        $now = time();
        $expiration = $now + (config('jwt.expiration') * 60);

        // Incluye únicamente identificador y rol, sin datos personales.
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $expiration,
        ];

        return JWT::encode($payload, config('jwt.secret'), config('jwt.algorithm'));
    }

    /** Verifica la firma y la expiración del token. */
    public function validate(string $token): object
    {
        return JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algorithm')));
    }
}
