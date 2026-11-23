<?php
namespace App\Application\UseCases;

use App\Application\Ports\PasswordHasher;
use App\Application\Ports\TokenIssuer;
use App\Application\Ports\UserRepository;
use App\Domain\Users\UserAccount;
use App\Exceptions\ApiException;

final class LoginUser
{
    public function __construct(
        private readonly UserRepository $users,
        private readonly PasswordHasher $passwords,
        private readonly TokenIssuer $tokens,
    ) {}

    /** @return array{token: string, user: UserAccount} */
    public function execute(string $username, string $password): array
    {
        // Normaliza el nombre de usuario antes de consultar el repositorio.
        $user = $this->users->findByUsername(strtolower(trim($username)));

        // Usa un mensaje genérico para no revelar si existe el usuario.
        if (!$user || !$this->passwords->verify($password, $user->passwordHash)) {
            throw new ApiException(
                'INVALID_CREDENTIALS',
                'Las credenciales de acceso son incorrectas.',
                401
            );
        }

        return ['token' => $this->tokens->generate($user), 'user' => $user];
    }
}
