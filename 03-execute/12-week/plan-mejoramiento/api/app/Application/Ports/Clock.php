<?php

namespace App\Application\Ports;

use DateTimeImmutable;

/** Provides the current instant (UTC) used by the business rules. */
interface Clock
{
    public function now(): DateTimeImmutable;
}
