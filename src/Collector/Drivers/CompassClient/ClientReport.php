<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\CompassClient;

use Maarheeze\Compass\Collector\SiteReports\CollectedSiteEndpoint;

readonly class ClientReport
{
    /**
     * @param list<CollectedSiteEndpoint> $endpoints
     */
    public function __construct(
        public bool $appDebug,
        public string $appEnv,
        public string $appName,
        public array $endpoints,
        public bool $maintenanceMode,
    ) {
    }
}
