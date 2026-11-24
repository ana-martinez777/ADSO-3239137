<?php

namespace Tests\Unit\Domain;

use App\Domain\Loans\LoanPolicy;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class LoanOverduePolicyTest extends TestCase
{
    public function test_athlete_without_overdue_items_can_receive_a_loan(): void
    {
        LoanPolicy::assertNoOverdue(false);
        self::assertTrue(true);
    }

    public function test_athlete_with_overdue_items_is_blocked(): void
    {
        $this->expectException(ApiException::class);
        LoanPolicy::assertNoOverdue(true);
    }
}
