<?php

namespace App\Domain\Shared;

use App\Exceptions\ApiException;
use DateTimeImmutable;

/** Optional period filter: both dates or none, and the start never after the end. */
final class DateRange
{
    private function __construct(
        public readonly ?DateTimeImmutable $from,
        public readonly ?DateTimeImmutable $to,
    ) {}

    public static function fromFilters(?string $from, ?string $to): self
    {
        $hasFrom = $from !== null && $from !== '';
        $hasTo = $to !== null && $to !== '';

        if ($hasFrom !== $hasTo) {
            throw new ApiException('VALIDATION_ERROR', 'Debes enviar conjuntamente las fechas from y to.', 422);
        }
        if (!$hasFrom) {
            return new self(null, null);
        }

        $start = new DateTimeImmutable($from);
        $end = new DateTimeImmutable($to);
        if ($start > $end) {
            throw new ApiException('INVALID_DATE_RANGE', 'La fecha inicial no puede ser posterior a la fecha final.', 422);
        }

        return new self($start, $end);
    }

    public function isEmpty(): bool
    {
        return $this->from === null;
    }
}
