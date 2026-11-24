<?php

namespace App\Domain\Loans;

use App\Exceptions\ApiException;
use DateTimeImmutable;

final class LoanPolicy
{
    public const MAX_PENDING_UNITS = 3;
    public const TERM_HOURS = 48;

    public static function assertWithinPendingLimit(int $currentPending, int $requested): void
    {
        if ($requested < 1 || $currentPending + $requested > self::MAX_PENDING_UNITS) {
            throw new ApiException(
                'ATHLETE_LIMIT_EXCEEDED',
                'El deportista no puede tener más de tres unidades pendientes de devolución.',
                422
            );
        }
    }

    public static function assertNoOverdue(bool $hasOverdue): void
    {
        if ($hasOverdue) {
            throw new ApiException(
                'ATHLETE_HAS_OVERDUE_LOANS',
                'El deportista tiene préstamos vencidos pendientes de devolución.',
                409
            );
        }
    }

    public static function assertUniqueEquipment(array $items): void
    {
        $ids = array_map(static fn (array $item): string => (string) $item['equipment_id'], $items);
        if (count($ids) !== count(array_unique($ids))) {
            throw new ApiException(
                'DUPLICATE_EQUIPMENT_IN_LOAN',
                'El mismo implemento no puede repetirse en un préstamo.',
                422
            );
        }
    }

    public static function dueAt(DateTimeImmutable $loanedAt): DateTimeImmutable
    {
        return $loanedAt->modify('+' . self::TERM_HOURS . ' hours');
    }

    public static function itemStatus(?DateTimeImmutable $returnedAt, DateTimeImmutable $dueAt, DateTimeImmutable $now): string
    {
        if ($returnedAt !== null) {
            return 'returned';
        }

        return $dueAt <= $now ? 'overdue' : 'pending';
    }

    public static function loanStatus(array $items, DateTimeImmutable $now): string
    {
        if ($items === []) {
            return 'returned';
        }

        $statuses = array_map(
            static fn (array $item): string => self::itemStatus($item['returned_at'], $item['due_at'], $now),
            $items
        );

        if (in_array('overdue', $statuses, true)) {
            return 'overdue';
        }

        return count(array_filter($statuses, static fn (string $status): bool => $status !== 'returned')) === 0
            ? 'returned'
            : 'pending';
    }
}
