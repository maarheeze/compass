<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Carbon\CarbonImmutable;

readonly class CollectedScheduler
{
    /**
     * @param list<CollectedScheduledTask> $tasks
     */
    public function __construct(
        public ?CarbonImmutable $lastRanAt,
        public array $tasks,
    ) {
    }
}
