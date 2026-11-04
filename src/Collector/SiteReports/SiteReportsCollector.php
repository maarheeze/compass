<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Models\Site;

interface SiteReportsCollector
{
    /**
     * @throws CollectingFailed
     */
    public function siteReport(Site $site): CollectedSiteReport;
}
