<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector;

use Closure;

readonly class WaitsForRequestBudget
{
    /**
     * @param Closure(LockedJob): mixed $next
     */
    public function handle(LockedJob $lockedJob, Closure $next): void
    {
        try {
            $next($lockedJob);
        } catch (RequestBudgetExhausted $requestBudgetExhausted) {
            $lockedJob->releaseAfter($requestBudgetExhausted->secondsUntilRoom);
        }
    }
}
