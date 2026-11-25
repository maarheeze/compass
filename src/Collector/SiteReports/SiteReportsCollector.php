<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Models\SiteReport;

interface SiteReportsCollector
{
    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function siteReport(SiteReport $siteReport): CollectedSiteReport;
}
