<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Collector\Forge\ForgeRequestFailed;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;
use Maarheeze\Compass\Collector\RunId;

class CollectSites implements ShouldQueue
{
    use Queueable;

    public function __construct(
        private readonly RunId $runId,
    ) {
    }

    /**
     * @throws ForgeRequestFailed
     */
    public function handle(
        SitesQuery $sitesQuery,
        SiteSynchronizer $siteSynchronizer,
    ): void {
        foreach ($sitesQuery->pages() as $sitesQueryResult) {
            $siteSynchronizer->storeSites(
                $sitesQueryResult->forgeSites,
                $this->runId,
            );
        }

        $siteSynchronizer->markUncollectedMissing($this->runId);
    }
}
