<?php

namespace App\Domain\Shared;

use App\Exceptions\ApiException;

/** Number of units in an operation: always a whole number greater than zero. */
final class Quantity
{
    private function __construct(public readonly int $value) {}

    public static function of(int $value): self
    {
        if ($value < 1) {
            throw new ApiException('VALIDATION_ERROR', 'La cantidad debe ser un número entero mayor que cero.', 422);
        }

        return new self($value);
    }

    /** @param int[] $values */
    public static function sum(array $values): int
    {
        return array_sum(array_map(static fn (int $value): int => self::of($value)->value, $values));
    }
}
