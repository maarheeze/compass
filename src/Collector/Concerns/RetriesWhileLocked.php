<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Concerns;

use Maarheeze\Compass\Collector\JobLock;

trait RetriesWhileLocked
{
    public int $maxExceptions = 1;
    public int $tries = 0;

    /**
     * @return array<int, class-string>
     */
    public function middleware(): array
    {
        return [JobLock::class];
    }
}
