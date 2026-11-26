<?php
namespace App\Domain\Users;

/** A user that does not depend on Eloquent or Laravel. */
final class UserAccount
{
    public function __construct(
        public readonly string $id,
        public readonly string $username,
        public readonly string $passwordHash,
        public readonly string $role,
    ) {}
}
