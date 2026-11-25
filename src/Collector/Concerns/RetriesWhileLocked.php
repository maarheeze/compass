<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Concerns;

use Maarheeze\Compass\Collector\JobLock;
use Maarheeze\Compass\Collector\RecordRunResult;
use Maarheeze\Compass\Collector\WaitsForRequestBudget;

trait RetriesWhileLocked
{
    public int $maxExceptions = 1;
    public int $tries = 0;

    /**
     * @return array<int, class-string>
     */
    public function middleware(): array
    {
        return [
            WaitsForRequestBudget::class,
            JobLock::class,
            RecordRunResult::class,
        ];
    }

    public function releaseAfter(int $seconds): void
    {
        $this->release($seconds);
    }
}
