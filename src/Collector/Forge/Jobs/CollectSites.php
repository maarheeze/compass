<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;
use Maarheeze\Compass\Collector\Sites\SitesCollectorManager;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectSites implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
        private readonly string $sourceKey,
    ) {
    }

    /**
     * @throws CollectingFailed
     */
    public function handle(
        CollectorLogger $collectorLogger,
        SitesCollectorManager $sitesCollectorManager,
        SiteSynchronizer $siteSynchronizer,
    ): void {
        $sitesCollector = $sitesCollectorManager->collector($this->sourceKey);

        $collectorLogger->info('Sites collector started');

        foreach ($sitesCollector->sites() as $collectedSites) {
            $siteSynchronizer->storeSites($collectedSites, $this->runId);

            $collectorLogger->debug('Sites page collected', [
                'site_count' => $collectedSites->count(),
            ]);
        }

        $siteSynchronizer->markUncollectedMissing($this->runId);

        $collectorLogger->info('Sites collector ended');

        Assert::isInstanceOf($this->batch(), Batch::class)->add([new InspectSites()]);
    }
}
