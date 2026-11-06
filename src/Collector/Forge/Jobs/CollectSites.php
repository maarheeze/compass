<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Forge\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;
use Maarheeze\Compass\Collector\Sites\SitesCollectorManager;
use Maarheeze\Compass\Collector\SourceConfigs;
use Maarheeze\Uuid\Uuid;

class CollectSites implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        private readonly Uuid $runId,
        private readonly string $source,
    ) {
    }

    /**
     * @throws CollectingFailed
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Dispatcher $events,
        SitesCollectorManager $sitesCollectorManager,
        SiteSynchronizer $siteSynchronizer,
        SourceConfigs $sourceConfigs,
    ): void {
        $sitesCollector = $sitesCollectorManager->collector($this->source);
        $sourceConfig = $sourceConfigs->get($this->source);

        $collectorLogger->info('Sites collector started');

        foreach ($sitesCollector->sites() as $collectedSites) {
            $siteSynchronizer->storeSites($collectedSites, $sourceConfig, $this->runId);

            $collectorLogger->debug('Sites page collected', [
                'site_count' => $collectedSites->count(),
            ]);
        }

        $siteSynchronizer->markUncollectedMissing($this->source, $this->runId);

        $events->dispatch(new SitesCollected($this->source));

        $collectorLogger->info('Sites collector ended');
    }
}
