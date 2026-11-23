<?php
namespace App\Domain\Users;

/** Representa un usuario sin depender de Eloquent ni de Laravel. */
final class UserAccount
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $passwordHash,
        public readonly string $role,
    ) {}
}
