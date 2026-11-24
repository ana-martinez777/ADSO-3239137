<?php

namespace Tests\Unit\Domain;

use App\Domain\Loans\LoanPolicy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoanAggregateStatusTest extends TestCase
{
    public function test_any_overdue_item_makes_loan_overdue(): void
    {
        $now = new DateTimeImmutable('2026-11-23T10:00:00+00:00');
        self::assertSame('overdue', LoanPolicy::loanStatus([
            ['returned_at' => null, 'due_at' => $now->modify('-1 minute')],
            ['returned_at' => null, 'due_at' => $now->modify('+1 day')],
        ], $now));
    }

    public function test_all_returned_items_make_loan_returned(): void
    {
        $now = new DateTimeImmutable('2026-11-23T10:00:00+00:00');
        self::assertSame('returned', LoanPolicy::loanStatus([
            ['returned_at' => $now, 'due_at' => $now->modify('-1 day')],
        ], $now));
    }

    public function test_returned_and_pending_items_make_loan_pending(): void
    {
        $now = new DateTimeImmutable('2026-11-23T10:00:00+00:00');
        self::assertSame('pending', LoanPolicy::loanStatus([
            ['returned_at' => $now, 'due_at' => $now->modify('-1 day')],
            ['returned_at' => null, 'due_at' => $now->modify('+1 day')],
        ], $now));
    }
}
