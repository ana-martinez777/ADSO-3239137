<?php

namespace App\Infrastructure\Persistence;

use App\Application\Ports\SportsGearRepository;
use App\Domain\Inventory\EquipmentInventory;
use App\Domain\Loans\LoanPolicy;
use App\Exceptions\ApiException;
use App\Models\Athlete;
use App\Models\Category;
use App\Models\Equipment;
use App\Models\EquipmentReferenceHistory;
use App\Models\Loan;
use App\Models\LoanItem;
use Carbon\CarbonImmutable;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\DB;

final class EloquentSportsGearRepository implements SportsGearRepository
{
    public function categories(): array
    {
        return Category::query()->orderBy('name')->get(['id', 'name'])->toArray();
    }

    public function equipmentList(array $filters, string $role): array
    {
        $status = $filters['status'] ?? 'active';
        if ($role === 'coach' && $status !== 'active') {
            throw new ApiException('FORBIDDEN_ROLE', 'No tienes permisos para consultar implementos inactivos.', 403);
        }

        $query = Equipment::query()->with('category');
        if ($status === 'active') {
            $query->whereNull('deleted_at');
        } elseif ($status === 'inactive') {
            $query->whereNotNull('deleted_at');
        }
        if (!empty($filters['search'])) {
            $query->where('reference', 'like', '%' . trim($filters['search']) . '%');
        }
        if (!empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        return $query->orderBy('reference')->get()->map(fn (Equipment $equipment): array => $this->equipmentData($equipment))->all();
    }

    public function equipmentFind(string $id, string $role): array
    {
        $query = Equipment::query()->with('category')->whereKey($id);
        if ($role === 'coach') {
            $query->whereNull('deleted_at');
        }
        $equipment = $query->first();
        if (!$equipment) {
            throw new ApiException('NOT_FOUND', 'El implemento solicitado no existe.', 404);
        }
        return $this->equipmentData($equipment);
    }

    public function equipmentCreate(array $data): array
    {
        try {
            return DB::transaction(function () use ($data): array {
            if (!Category::query()->whereKey($data['category_id'])->exists()) {
                throw new ApiException('CATEGORY_NOT_FOUND', 'La categoría seleccionada no existe.', 404);
            }
            if (EquipmentReferenceHistory::query()->whereKey($data['reference'])->exists()) {
                throw new ApiException('REFERENCE_ALREADY_EXISTS', 'La referencia ya fue utilizada anteriormente.', 409);
            }

            $equipment = Equipment::query()->create([
                'reference' => $data['reference'],
                'category_id' => $data['category_id'],
                'total_units' => $data['total_units'],
                'available_units' => $data['total_units'],
            ]);
            EquipmentReferenceHistory::query()->create([
                'reference' => $data['reference'],
                'equipment_id' => $equipment->id,
            ]);

                return $this->equipmentData($equipment->load('category'));
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23000') {
                throw new ApiException('REFERENCE_ALREADY_EXISTS', 'La referencia ya fue utilizada anteriormente.', 409);
            }
            throw $exception;
        }
    }

    public function equipmentUpdate(string $id, array $data): array
    {
        try {
            return DB::transaction(function () use ($id, $data): array {
            $equipment = Equipment::query()->whereKey($id)->lockForUpdate()->first();
            if (!$equipment) {
                throw new ApiException('NOT_FOUND', 'El implemento solicitado no existe.', 404);
            }
            if (!Category::query()->whereKey($data['category_id'])->exists()) {
                throw new ApiException('CATEGORY_NOT_FOUND', 'La categoría seleccionada no existe.', 404);
            }

            if ($data['reference'] !== $equipment->reference) {
                if (EquipmentReferenceHistory::query()->whereKey($data['reference'])->exists()) {
                    throw new ApiException('REFERENCE_ALREADY_EXISTS', 'La referencia ya fue utilizada anteriormente.', 409);
                }
                EquipmentReferenceHistory::query()->create([
                    'reference' => $data['reference'],
                    'equipment_id' => $equipment->id,
                ]);
            }

            $loanedUnits = (int) $equipment->total_units - (int) $equipment->available_units;
            $availableUnits = EquipmentInventory::availableAfterTotalChange((int) $data['total_units'], $loanedUnits);
            $equipment->update([
                'reference' => $data['reference'],
                'category_id' => $data['category_id'],
                'total_units' => $data['total_units'],
                'available_units' => $availableUnits,
            ]);

                return $this->equipmentData($equipment->fresh()->load('category'));
            });
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23000') {
                throw new ApiException('REFERENCE_ALREADY_EXISTS', 'La referencia ya fue utilizada anteriormente.', 409);
            }
            throw $exception;
        }
    }

    public function equipmentDelete(string $id): void
    {
        $equipment = Equipment::query()->whereKey($id)->first();
        if (!$equipment) {
            throw new ApiException('NOT_FOUND', 'El implemento solicitado no existe.', 404);
        }
        if ($equipment->deleted_at === null) {
            $equipment->update(['deleted_at' => CarbonImmutable::now('UTC')]);
        }
    }

    public function athletesList(array $filters): array
    {
        $query = Athlete::query();
        if (!empty($filters['search'])) {
            $query->where('full_name', 'like', '%' . trim($filters['search']) . '%');
        }
        if (!empty($filters['document_number'])) {
            $query->where('document_number', $filters['document_number']);
        }
        return $query->orderBy('full_name')->get(['id', 'full_name', 'document_number'])->toArray();
    }

    public function athleteFind(string $id): array
    {
        $athlete = Athlete::query()->find($id);
        if (!$athlete) {
            throw new ApiException('NOT_FOUND', 'El deportista solicitado no existe.', 404);
        }
        return $athlete->only(['id', 'full_name', 'document_number']);
    }

    public function athleteCreate(array $data): array
    {
        try {
            $athlete = Athlete::query()->create($data);
        } catch (QueryException $exception) {
            if (($exception->errorInfo[0] ?? null) === '23000') {
                throw new ApiException('DOCUMENT_NUMBER_ALREADY_EXISTS', 'El documento ya está registrado.', 409);
            }
            throw $exception;
        }
        return $athlete->only(['id', 'full_name', 'document_number']);
    }

    public function loanCreate(string $athleteId, string $userId, array $items): array
    {
        return DB::transaction(function () use ($athleteId, $userId, $items): array {
            $athlete = Athlete::query()->whereKey($athleteId)->lockForUpdate()->first();
            if (!$athlete) {
                throw new ApiException('NOT_FOUND', 'El deportista solicitado no existe.', 404);
            }

            LoanPolicy::assertUniqueEquipment($items);
            $now = CarbonImmutable::now('UTC');
            $overdueExists = LoanItem::query()->whereNull('returned_at')
                ->where('due_at', '<=', $now)
                ->whereHas('loan', fn ($query) => $query->where('athlete_id', $athleteId))
                ->exists();
            LoanPolicy::assertNoOverdue($overdueExists);

            $currentPending = (int) DB::table('loan_items')
                ->join('loans', 'loans.id', '=', 'loan_items.loan_id')
                ->where('loans.athlete_id', $athleteId)
                ->whereNull('loan_items.returned_at')
                ->sum('loan_items.quantity');
            $requestedUnits = array_sum(array_map(static fn (array $item): int => (int) $item['quantity'], $items));
            LoanPolicy::assertWithinPendingLimit($currentPending, $requestedUnits);

            $equipmentIds = array_map(static fn (array $item): string => (string) $item['equipment_id'], $items);
            sort($equipmentIds);
            $equipmentRecords = Equipment::query()->whereIn('id', $equipmentIds)
                ->orderBy('id')->lockForUpdate()->with('category')->get()->keyBy('id');
            if ($equipmentRecords->count() !== count($equipmentIds)) {
                throw new ApiException('NOT_FOUND', 'Uno o varios implementos no existen.', 404);
            }

            foreach ($items as $item) {
                $equipment = $equipmentRecords->get($item['equipment_id']);
                EquipmentInventory::assertCanLoan(
                    $equipment->deleted_at === null,
                    (int) $equipment->available_units,
                    (int) $item['quantity']
                );
            }

            $loan = Loan::query()->create([
                'athlete_id' => $athleteId,
                'user_id' => $userId,
                'loaned_at' => $now,
            ]);
            $dueAt = $now->addHours(LoanPolicy::TERM_HOURS);

            foreach ($items as $item) {
                $equipment = $equipmentRecords->get($item['equipment_id']);
                LoanItem::query()->create([
                    'loan_id' => $loan->id,
                    'equipment_id' => $equipment->id,
                    'equipment_reference' => $equipment->reference,
                    'category_name' => $equipment->category->name,
                    'quantity' => $item['quantity'],
                    'due_at' => $dueAt,
                ]);
                $equipment->decrement('available_units', (int) $item['quantity']);
            }

            return $this->loanData($loan->fresh()->load('loanItems'), true);
        });
    }

    public function loansList(array $filters): array
    {
        $this->assertDateRange($filters);
        $query = Loan::query()->with('loanItems');
        if (!empty($filters['from'])) {
            $query->whereDate('loaned_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('loaned_at', '<=', $filters['to']);
        }
        foreach (['athlete_id', 'user_id'] as $field) {
            if (!empty($filters[$field])) {
                $query->where($field, $filters[$field]);
            }
        }
        $now = CarbonImmutable::now('UTC');
        $loans = $query->orderByDesc('loaned_at')->get()->map(fn (Loan $loan): array => $this->loanData($loan, false, $now))->all();
        if (!empty($filters['status'])) {
            $loans = array_values(array_filter($loans, fn (array $loan): bool => $loan['status'] === $filters['status']));
        }
        return $loans;
    }

    public function loanFind(string $id): array
    {
        $loan = Loan::query()->with('loanItems')->find($id);
        if (!$loan) {
            throw new ApiException('NOT_FOUND', 'El préstamo solicitado no existe.', 404);
        }
        return $this->loanData($loan, true);
    }

    public function overdueList(?string $athleteId = null): array
    {
        $now = CarbonImmutable::now('UTC');
        $query = LoanItem::query()->with('loan.athlete')
            ->whereNull('returned_at')->where('due_at', '<=', $now);
        if ($athleteId !== null) {
            $query->whereHas('loan', fn ($loanQuery) => $loanQuery->where('athlete_id', $athleteId));
        }
        return $query->orderBy('due_at')->get()->map(function (LoanItem $item) use ($now): array {
                return [
                    'item_id' => $item->id,
                    'loan_id' => $item->loan_id,
                    'athlete_id' => $item->loan->athlete_id,
                    'athlete_name' => $item->loan->athlete->full_name,
                    'equipment_reference' => $item->equipment_reference,
                    'category_name' => $item->category_name,
                    'due_at' => $this->dateString($item->due_at),
                    'days_late' => (int) $item->due_at->diffInDays($now),
                ];
            })->all();
    }

    public function loanItemReturn(string $loanId, string $itemId): array
    {
        return DB::transaction(function () use ($loanId, $itemId): array {
            $item = LoanItem::query()->where('loan_id', $loanId)->whereKey($itemId)->lockForUpdate()->first();
            if (!$item) {
                throw new ApiException('NOT_FOUND', 'El detalle del préstamo no existe.', 404);
            }
            if ($item->returned_at !== null) {
                throw new ApiException('LOAN_ITEM_ALREADY_RETURNED', 'El detalle del préstamo ya fue devuelto.', 409);
            }

            $equipment = Equipment::query()->whereKey($item->equipment_id)->lockForUpdate()->first();
            if (!$equipment) {
                throw new ApiException('NOT_FOUND', 'El implemento asociado no existe.', 404);
            }
            $item->update(['returned_at' => CarbonImmutable::now('UTC')]);
            $equipment->increment('available_units', (int) $item->quantity);

            return [
                'id' => $item->id,
                'loan_id' => $item->loan_id,
                'equipment_id' => $item->equipment_id,
                'equipment_reference' => $item->equipment_reference,
                'quantity' => (int) $item->quantity,
                'due_at' => $this->dateString($item->due_at),
                'returned_at' => $this->dateString($item->fresh()->returned_at),
                'status' => 'returned',
            ];
        });
    }

    public function mostRequested(array $filters): array
    {
        $this->assertDateRange($filters);
        $query = DB::table('loan_items')->join('loans', 'loans.id', '=', 'loan_items.loan_id')
            ->select('loan_items.equipment_reference', 'loan_items.category_name')
            ->selectRaw('SUM(loan_items.quantity) AS total_requested')
            ->groupBy('loan_items.equipment_reference', 'loan_items.category_name')
            ->orderByDesc('total_requested');
        if (!empty($filters['from'])) {
            $query->whereDate('loans.loaned_at', '>=', $filters['from']);
        }
        if (!empty($filters['to'])) {
            $query->whereDate('loans.loaned_at', '<=', $filters['to']);
        }
        return $query->get()->map(fn ($row): array => [
            'equipment_reference' => $row->equipment_reference,
            'category_name' => $row->category_name,
            'total_requested' => (int) $row->total_requested,
        ])->all();
    }

    private function equipmentData(Equipment $equipment): array
    {
        return [
            'id' => $equipment->id,
            'reference' => $equipment->reference,
            'category' => ['id' => $equipment->category->id, 'name' => $equipment->category->name],
            'category_id' => $equipment->category_id,
            'total_units' => (int) $equipment->total_units,
            'available_units' => (int) $equipment->available_units,
            'deleted_at' => $this->dateString($equipment->deleted_at),
        ];
    }

    private function loanData(Loan $loan, bool $includeItems, ?CarbonImmutable $now = null): array
    {
        $now ??= CarbonImmutable::now('UTC');
        $items = $loan->loanItems->map(fn (LoanItem $item): array => [
            'id' => $item->id,
            'loan_id' => $item->loan_id,
            'equipment_id' => $item->equipment_id,
            'equipment_reference' => $item->equipment_reference,
            'category_name' => $item->category_name,
            'quantity' => (int) $item->quantity,
            'due_at' => $this->dateString($item->due_at),
            'returned_at' => $this->dateString($item->returned_at),
            'status' => LoanPolicy::itemStatus(
                $item->returned_at ? CarbonImmutable::parse($item->returned_at) : null,
                CarbonImmutable::parse($item->due_at),
                $now
            ),
        ])->all();
        $statusItems = array_map(static fn (array $item): array => [
            'returned_at' => $item['returned_at'] ? CarbonImmutable::parse($item['returned_at']) : null,
            'due_at' => CarbonImmutable::parse($item['due_at']),
        ], $items);
        $data = [
            'id' => $loan->id,
            'athlete_id' => $loan->athlete_id,
            'user_id' => $loan->user_id,
            'loaned_at' => $this->dateString($loan->loaned_at),
            'status' => LoanPolicy::loanStatus($statusItems, $now),
        ];
        if ($includeItems) {
            $data['items'] = $items;
        }
        return $data;
    }

    private function assertDateRange(array $filters): void
    {
        $hasFrom = !empty($filters['from']);
        $hasTo = !empty($filters['to']);
        if ($hasFrom !== $hasTo) {
            throw new ApiException('VALIDATION_ERROR', 'Debes enviar conjuntamente las fechas from y to.', 422);
        }
        if ($hasFrom && CarbonImmutable::parse($filters['from'])->gt(CarbonImmutable::parse($filters['to']))) {
            throw new ApiException('INVALID_DATE_RANGE', 'La fecha inicial no puede ser posterior a la fecha final.', 422);
        }
    }

    private function dateString(mixed $date): ?string
    {
        return $date ? CarbonImmutable::parse($date)->utc()->format('Y-m-d\TH:i:s.u\Z') : null;
    }
}
