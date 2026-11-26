<?php
namespace App\Services\Auth;

use App\Application\Ports\TokenIssuer;
use App\Domain\Users\UserAccount;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService implements TokenIssuer
{
    /** Issues a JWT for the authenticated user. */
    public function generate(UserAccount $user): string
    {
        // Computes the issue and expiration times of the token.
        $now = time();
        $expiration = $now + (config('jwt.expiration') * 60);

        // Includes only the identifier and the role, no personal data.
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'role' => $user->role,
            'iat' => $now,
            'exp' => $expiration,
        ];

        return JWT::encode($payload, config('jwt.secret'), config('jwt.algorithm'));
    }

    /** Verifies the signature and the expiration of the token. */
    public function validate(string $token): object
    {
        return JWT::decode($token, new Key(config('jwt.secret'), config('jwt.algorithm')));
    }
}
