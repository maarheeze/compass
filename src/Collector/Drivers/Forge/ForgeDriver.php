<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\Forge;

use Generator;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainsCollector;
use Maarheeze\Compass\Collector\SiteProcesses\CollectedSiteProcesses;
use Maarheeze\Compass\Collector\SiteProcesses\SiteProcessesCollector;
use Maarheeze\Compass\Collector\Sites\SitesCollector;
use Maarheeze\Compass\Models\Site;

readonly class ForgeDriver implements SiteDomainsCollector, SiteProcessesCollector, SitesCollector
{
    public function __construct(
        private ForgeClient $forgeClient,
        private string $organisation,
        private string $serverPattern,
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

    /**
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function siteProcesses(Site $site): CollectedSiteProcesses
    {
        $siteProcessesQuery = new SiteProcessesQuery($this->forgeClient, $this->organisation);

        try {
            return $siteProcessesQuery->siteProcesses($site);
        } catch (ForgeRequestFailed $forgeRequestFailed) {
            throw CollectingFailed::fromThrowable($forgeRequestFailed);
        }
    }

    /**
     * @return Generator<int, CollectedSiteCollection>
     *
     * @throws CollectingFailed
     * @throws RequestBudgetExhausted
     */
    public function sites(): Generator
    {
        $sitesQuery = new SitesQuery($this->forgeClient, $this->organisation);

        try {
            foreach ($sitesQuery->pages() as $sitesQueryResult) {
                yield $sitesQueryResult->collectedSites->onServersMatching($this->serverPattern);
            }
        } catch (ForgeRequestFailed $forgeRequestFailed) {
            throw CollectingFailed::fromThrowable($forgeRequestFailed);
        }
    }
}
