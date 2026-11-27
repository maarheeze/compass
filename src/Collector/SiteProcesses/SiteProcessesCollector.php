<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteProcesses;

use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Models\Site;

interface SiteProcessesCollector
{
    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function siteProcesses(Site $site): CollectedSiteProcesses;
}
