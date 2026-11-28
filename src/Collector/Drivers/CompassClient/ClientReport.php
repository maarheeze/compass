<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Maarheeze\Compass\Collector\SiteReports\CollectedScheduler;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;

readonly class ClientReport
{
    /**
     * @param list<CollectedSiteEndpoint>|null $endpoints
     */
    public function __construct(
        public ?bool $appDebug,
        public ?string $appEnv,
        public ?string $appName,
        public ?array $endpoints,
        public ?bool $maintenanceMode,
        public ?string $queueDriver,
        public ?CollectedScheduler $scheduler,
    ) {
    }
}
