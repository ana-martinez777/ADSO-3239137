<?php

namespace Tests\Unit\Domain;

use App\Domain\Loans\LoanPolicy;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class LoanPendingLimitTest extends TestCase
{
    public function test_three_pending_units_are_allowed(): void
    {
        LoanPolicy::assertWithinPendingLimit(1, 2);
        self::assertTrue(true);
    }

    public function test_more_than_three_pending_units_are_rejected(): void
    {
        $this->expectException(ApiException::class);
        LoanPolicy::assertWithinPendingLimit(2, 2);
    }
}
