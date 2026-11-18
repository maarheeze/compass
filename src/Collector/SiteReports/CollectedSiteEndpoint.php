<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;

readonly class CollectedSiteEndpoint
{
    public function __construct(
        public EndpointPath $path,
        public int $frequencyMinutes,
    ) {
    }
}
