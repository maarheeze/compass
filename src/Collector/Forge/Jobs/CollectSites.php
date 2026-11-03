<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Forge\ForgeRequestFailed;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectSites implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
    ) {
    }

    /**
     * @throws ForgeRequestFailed
     */
    public function handle(
        CollectorLogger $collectorLogger,
        SitesQuery $sitesQuery,
        SiteSynchronizer $siteSynchronizer,
    ): void {
        $collectorLogger->info('Sites collector started');

        foreach ($sitesQuery->pages() as $sitesQueryResult) {
            $siteSynchronizer->storeSites(
                $sitesQueryResult->forgeSites,
                $this->runId,
            );

            $collectorLogger->debug('Sites page collected', [
                'site_count' => $sitesQueryResult->forgeSites->count(),
            ]);
        }

        $siteSynchronizer->markUncollectedMissing($this->runId);

        $collectorLogger->info('Sites collector ended');

        Assert::isInstanceOf($this->batch(), Batch::class)->add([new InspectSites()]);
    }
}
