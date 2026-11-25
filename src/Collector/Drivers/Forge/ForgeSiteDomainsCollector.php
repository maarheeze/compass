<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\Forge;

use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainsCollector;
use Maarheeze\Compass\Models\Site;

readonly class ForgeSiteDomainsCollector implements SiteDomainsCollector
{
    public function __construct(
        private ForgeClient $forgeClient,
        private string $organisation,
    ) {
    }

    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function siteDomains(Site $site): CollectedSiteDomainCollection
    {
        $siteDomainsQuery = new SiteDomainsQuery($this->forgeClient, $this->organisation);

        try {
            return $siteDomainsQuery->siteDomains($site);
        } catch (ForgeRequestFailed $forgeRequestFailed) {
            throw CollectingFailed::fromThrowable($forgeRequestFailed);
        }
    }
}
