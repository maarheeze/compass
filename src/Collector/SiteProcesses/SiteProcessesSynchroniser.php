<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteProcesses;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class SiteProcessesSynchroniser
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function storeSiteProcesses(Site $site, CollectedSiteProcesses $collectedSiteProcesses): void
    {
        $site->queue_worker_state = $collectedSiteProcesses->queueWorkerState;
        $site->processes_collected_at = CarbonImmutable::instance($this->clock->now());
        $site->save();
    }
}
