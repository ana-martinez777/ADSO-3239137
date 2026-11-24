<?php

namespace Tests\Unit\Domain;

use App\Domain\Loans\LoanPolicy;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

final class LoanDueDateTest extends TestCase
{
    public function test_due_date_is_48_hours_after_loan_creation(): void
    {
        $loanedAt = new DateTimeImmutable('2026-11-23T10:00:00+00:00');
        self::assertSame('2026-11-25T10:00:00+00:00', LoanPolicy::dueAt($loanedAt)->format(DATE_ATOM));
    }
}
