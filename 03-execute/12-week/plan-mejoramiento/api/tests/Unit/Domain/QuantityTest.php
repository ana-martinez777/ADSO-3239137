<?php

namespace Tests\Unit\Domain;

use App\Domain\Shared\Quantity;
use App\Exceptions\ApiException;
use PHPUnit\Framework\TestCase;

final class QuantityTest extends TestCase
{
    public function test_positive_quantities_are_accepted_and_summed(): void
    {
        self::assertSame(2, Quantity::of(2)->value);
        self::assertSame(3, Quantity::sum([1, 2]));
    }

    public function test_zero_or_negative_quantities_are_rejected(): void
    {
        foreach ([0, -1] as $invalid) {
            try {
                Quantity::of($invalid);
                self::fail("Quantity {$invalid} should be rejected.");
            } catch (ApiException $exception) {
                self::assertSame('VALIDATION_ERROR', $exception->errorCode);
                self::assertSame(422, $exception->httpStatus);
            }
        }
    }
}
