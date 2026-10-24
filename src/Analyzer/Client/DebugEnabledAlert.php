<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyzer\Client;

use Maarheeze\Compass\Analyzer\Alert;
use Maarheeze\Compass\Models\Site;

readonly class DebugEnabledAlert implements Alert
{
    public function __construct(
        public Site $site,
    ) {
    }

    public function viewName(): string
    {
        return 'compass::components.alerts.debug-enabled';
    }
}
