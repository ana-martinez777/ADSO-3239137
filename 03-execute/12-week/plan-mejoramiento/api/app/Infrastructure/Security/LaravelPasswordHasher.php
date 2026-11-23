<?php
namespace App\Infrastructure\Security;

use App\Application\Ports\PasswordHasher;
use Illuminate\Support\Facades\Hash;

final class LaravelPasswordHasher implements PasswordHasher
{
    public function verify(string $plainPassword, string $passwordHash): bool
    {
        return Hash::check($plainPassword, $passwordHash);
    }

    public function hash(string $plainPassword): string
    {
        return Hash::make($plainPassword);
    }
}
