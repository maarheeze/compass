<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\SiteReports\CollectedScheduledTask;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;

readonly class ClientReport
{
    /**
     * @param list<CollectedSiteEndpoint> $endpoints
     * @param list<CollectedScheduledTask> $scheduledTasks
     */
    public function __construct(
        public ?bool $appDebug,
        public ?string $appEnv,
        public ?string $appName,
        public array $endpoints,
        public ?bool $maintenanceMode,
        public ?string $queueDriver,
        public array $scheduledTasks,
        public ?CarbonImmutable $schedulerLastRanAt,
    ) {
    }
}
