<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Closure;
use Illuminate\Queue\Middleware\WithoutOverlapping;

readonly class JobLock
{
    public function __construct(
        private int $lockExpiryMinutes,
        private int $lockRetrySeconds,
    ) {
    }

    /**
     * @param Closure(LockedJob): mixed $next
     */
    public function handle(LockedJob $lockedJob, Closure $next): void
    {
        new WithoutOverlapping($lockedJob->lockKey())
            ->releaseAfter($this->lockRetrySeconds)
            ->expireAfter($this->lockExpiryMinutes * 60)
            ->handle($lockedJob, $next);
    }
}
