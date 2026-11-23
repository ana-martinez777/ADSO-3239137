<?php
namespace App\Application\Ports;

use App\Domain\Users\UserAccount;

interface UserRepository
{
    public function findByUsername(string $username): ?UserAccount;
    public function createCoach(string $username, string $passwordHash): UserAccount;
    /** @return UserAccount[] */
    public function findCoaches(?string $search = null): array;
}
