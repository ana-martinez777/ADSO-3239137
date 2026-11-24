<?php

namespace App\Application\Ports;

interface SportsGearRepository
{
    public function categories(): array;
    public function equipmentList(array $filters, string $role): array;
    public function equipmentFind(string $id, string $role): array;
    public function equipmentCreate(array $data): array;
    public function equipmentUpdate(string $id, array $data): array;
    public function equipmentDelete(string $id): void;
    public function athletesList(array $filters): array;
    public function athleteFind(string $id): array;
    public function athleteCreate(array $data): array;
    public function loanCreate(string $athleteId, string $userId, array $items): array;
    public function loansList(array $filters): array;
    public function loanFind(string $id): array;
    public function overdueList(?string $athleteId = null): array;
    public function loanItemReturn(string $loanId, string $itemId): array;
    public function mostRequested(array $filters): array;
}
