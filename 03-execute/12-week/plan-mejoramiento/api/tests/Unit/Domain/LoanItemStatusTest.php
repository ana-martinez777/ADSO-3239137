<?php

namespace Tests\Unit\Domain;

use App\Domain\Loans\LoanPolicy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoanItemStatusTest extends TestCase
{
    public function test_returned_item_has_returned_status(): void
    {
        $now = new DateTimeImmutable('2026-11-23T10:00:00+00:00');
        self::assertSame('returned', LoanPolicy::itemStatus($now, $now->modify('-1 hour'), $now));
    }

    public function test_pending_item_becomes_overdue_at_due_time(): void
    {
        $now = new DateTimeImmutable('2026-11-23T10:00:00+00:00');
        self::assertSame('overdue', LoanPolicy::itemStatus(null, $now, $now));
    }

    public function test_item_before_due_date_is_pending(): void
    {
        $now = new DateTimeImmutable('2026-11-23T10:00:00+00:00');
        self::assertSame('pending', LoanPolicy::itemStatus(null, $now->modify('+1 hour'), $now));
    }
}
