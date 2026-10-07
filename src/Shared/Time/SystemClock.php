<?php

namespace App\Shared\Time;

final class SystemClock implements ClockInterface
{
    private readonly \DateTimeZone $timezone;

    public function __construct(
        string $timezone
    ) {
        $this->timezone = new \DateTimeZone($timezone);
    }

    public function now(): \DateTimeImmutable
    {
        return new \DateTimeImmutable('now', $this->timezone);
    }

    public function nowFormatted(): string
    {
        return $this->now()->format('Y-m-d\TH:i:s.v\Z');
    }
}
