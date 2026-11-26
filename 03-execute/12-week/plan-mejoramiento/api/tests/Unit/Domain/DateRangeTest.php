<?php

namespace Tests\Unit\Domain;

use App\Domain\Shared\DateRange;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class DateRangeTest extends TestCase
{
    public function test_both_dates_or_none_are_valid(): void
    {
        self::assertTrue(DateRange::fromFilters(null, null)->isEmpty());
        $range = DateRange::fromFilters('2026-11-01', '2026-11-30');
        self::assertFalse($range->isEmpty());
        self::assertSame('2026-11-01', $range->from->format('Y-m-d'));
    }

    public function test_only_one_date_is_rejected(): void
    {
        $this->expectExceptionObject(new ApiException('VALIDATION_ERROR', 'Debes enviar conjuntamente las fechas from y to.', 422));
        DateRange::fromFilters('2026-11-01', null);
    }

    public function test_start_after_end_is_rejected(): void
    {
        try {
            DateRange::fromFilters('2026-11-30', '2026-11-01');
            self::fail('An inverted range should be rejected.');
        } catch (ApiException $exception) {
            self::assertSame('INVALID_DATE_RANGE', $exception->errorCode);
        }
    }
}
