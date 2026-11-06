<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Drivers\Forge;

use Generator;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Sites\SitesCollector;

readonly class ForgeSitesCollector implements SitesCollector
{
    public function __construct(
        private ForgeClient $forgeClient,
        private string $organization,
    ) {
    }

    /**
     * @return Generator<int, CollectedSiteCollection>
     *
     * @throws CollectingFailed
     */
    public function sites(): Generator
    {
        $sitesQuery = new SitesQuery($this->forgeClient, $this->organization);

        try {
            foreach ($sitesQuery->pages() as $sitesQueryResult) {
                yield $sitesQueryResult->collectedSites;
            }
        } catch (ForgeRequestFailed $forgeRequestFailed) {
            throw CollectingFailed::fromThrowable($forgeRequestFailed);
        }
    }
}
