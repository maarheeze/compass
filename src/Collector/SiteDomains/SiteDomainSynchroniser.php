<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains;

use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Models\Site;
use Throwable;

readonly class SiteDomainSynchroniser
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function storeSiteDomains(Site $site, CollectedSiteDomainCollection $collectedSiteDomains): void
    {
        $this->connection->transaction(function () use ($site, $collectedSiteDomains): void {
            $droppedSiteDomains = $site->domains()
                ->whereNotIn('name', $collectedSiteDomains->names())
                ->get();

            foreach ($droppedSiteDomains as $droppedSiteDomain) {
                $droppedSiteDomain->delete();
            }

            foreach ($collectedSiteDomains as $collectedSiteDomain) {
                $this->storeSiteDomain($site, $collectedSiteDomain);
            }
        });
    }

    private function storeSiteDomain(Site $site, CollectedSiteDomain $collectedSiteDomain): void
    {
        $siteDomain = $site->domains()->firstOrNew(['name' => $collectedSiteDomain->name]);
        $siteDomain->type = $collectedSiteDomain->type;
        $siteDomain->status = $collectedSiteDomain->status;
        $siteDomain->save();
    }
}
