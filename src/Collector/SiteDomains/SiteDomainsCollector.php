<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains;

use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Models\Site;

interface SiteDomainsCollector
{
    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function siteDomains(Site $site): CollectedSiteDomainCollection;
}
