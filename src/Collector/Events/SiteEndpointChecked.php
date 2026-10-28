<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Events;

use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;

readonly class SiteEndpointChecked
{
    use SerializesModels;

    public function __construct(
        public SiteEndpoint $siteEndpoint,
        public SiteEndpointCheck $siteEndpointCheck,
    ) {
    }
}
