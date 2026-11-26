<?php
namespace App\Application\UseCases;

use App\Application\Ports\UserRepository;
use App\Domain\Users\UserAccount;
use App\Exceptions\ApiException;

final class RegisterCoach
{
    public function __construct(private readonly UserRepository $users) {}

    public function execute(string $username, string $plainPassword): UserAccount
    {
        // Normalizes the username as the contract requires.
        $username = strtolower(trim($username));

        if ($username === '') {
            throw new ApiException('VALIDATION_ERROR', 'El nombre de usuario es obligatorio.', 422);
        }

        if ($this->users->findByUsername($username)) {
            throw new ApiException('USERNAME_ALREADY_EXISTS', 'El nombre de usuario ya está registrado.', 409);
        }

        // The repository stores the password through the hashing adapter.
        return $this->users->createCoach($username, $plainPassword);
    }
}
