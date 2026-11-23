<?php
namespace App\Application\Ports;

interface PasswordHasher
{
    public function verify(string $plainPassword, string $passwordHash): bool;
    public function hash(string $plainPassword): string;
}
