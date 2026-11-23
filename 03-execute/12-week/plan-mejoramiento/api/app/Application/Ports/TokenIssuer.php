<?php
namespace App\Application\Ports;

use App\Domain\Users\UserAccount;

interface TokenIssuer
{
    public function generate(UserAccount $user): string;
}
