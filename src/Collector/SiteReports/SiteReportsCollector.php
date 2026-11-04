<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Models\SiteReport;

interface SiteReportsCollector
{
    /**
     * @throws CollectingFailed
     */
    public function siteReport(SiteReport $siteReport): CollectedSiteReport;
}
