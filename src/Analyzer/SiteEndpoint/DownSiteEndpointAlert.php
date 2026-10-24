<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\SiteEndpoint;

use Maarheeze\Compass\Analyzer\Alert;
use Maarheeze\Compass\Models\SiteEndpointCheck;

readonly class DownSiteEndpointAlert implements Alert
{
    public function __construct(
        public SiteEndpointCheck $siteEndpointCheck,
    ) {
    }

    public function viewName(): string
    {
        return 'compass::components.alerts.down-site-endpoint';
    }
}
