<?php
namespace App\Application\UseCases;

use App\Application\Ports\UserRepository;
use App\Domain\Users\UserAccount;

final class ListCoaches
{
    public function __construct(private readonly UserRepository $users) {}

    /** @return UserAccount[] */
    public function execute(?string $search = null): array
    {
        // Convierte un filtro vacío en una consulta sin filtro.
        $search = $search !== null ? trim($search) : null;
        return $this->users->findCoaches($search === '' ? null : $search);
    }
}
