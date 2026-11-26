<?php

namespace App\Infrastructure\Time;

use App\Application\Ports\Clock;
use Carbon\CarbonImmutable;
use DateTimeImmutable;

/** Clock adapter backed by the operating system time, always in UTC. */
final class SystemClock implements Clock
{
    public function now(): DateTimeImmutable
    {
        return CarbonImmutable::now('UTC');
    }
}
