<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteProcesses;

readonly class CollectedSiteProcesses
{
    public function __construct(
        public QueueWorkerState $queueWorkerState,
    ) {
    }
}
