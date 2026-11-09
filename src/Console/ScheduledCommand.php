<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Console;

use Carbon\CarbonImmutable;

readonly class ScheduledCommand
{
    public function __construct(
        public string $command,
        public string $mutexName,
        public CarbonImmutable $nextRunAt,
    ) {
    }
}
