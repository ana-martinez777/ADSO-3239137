<?php

namespace App\Application\UseCases;

use App\Application\Ports\SportsGearRepository;

final class ManageSportsGear
{
    public function __construct(private readonly SportsGearRepository $repository) {}

    public function categories(): array { return $this->repository->categories(); }
    public function equipmentList(array $filters, string $role): array { return $this->repository->equipmentList($filters, $role); }
    public function equipmentFind(string $id, string $role): array { return $this->repository->equipmentFind($id, $role); }
    public function equipmentCreate(array $data): array { return $this->repository->equipmentCreate($data); }
    public function equipmentUpdate(string $id, array $data): array { return $this->repository->equipmentUpdate($id, $data); }
    public function equipmentDelete(string $id): void { $this->repository->equipmentDelete($id); }
    public function athletesList(array $filters): array { return $this->repository->athletesList($filters); }
    public function athleteFind(string $id): array { return $this->repository->athleteFind($id); }
    public function athleteCreate(array $data): array { return $this->repository->athleteCreate($data); }
    public function loanCreate(string $athleteId, string $userId, array $items): array { return $this->repository->loanCreate($athleteId, $userId, $items); }
    public function loansList(array $filters): array { return $this->repository->loansList($filters); }
    public function loanFind(string $id): array { return $this->repository->loanFind($id); }
    public function overdueList(?string $athleteId = null): array { return $this->repository->overdueList($athleteId); }
    public function loanItemReturn(string $loanId, string $itemId): array { return $this->repository->loanItemReturn($loanId, $itemId); }
    public function mostRequested(array $filters): array { return $this->repository->mostRequested($filters); }
}
