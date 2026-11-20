<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Sites\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Collector\SiteDomains\Jobs\CollectSiteDomainsOfSites;
use Maarheeze\Compass\Collector\Sites\SitesCollectorManager;
use Maarheeze\Compass\Collector\Sites\SiteSynchroniser;
use Maarheeze\Compass\Collector\SourceConfigs;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectSites implements LockedJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;

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
        SiteSynchroniser $siteSynchroniser,
        SourceConfigs $sourceConfigs,
    ): void {
        $sitesCollector = $sitesCollectorManager->collector($this->source);
        $autoDeployGraceMinutes = $sourceConfigs
            ->get($this->source)
            ->integerSetting('auto_deploy_grace_minutes');

        $collectorLogger->info('Sites collector started');

        foreach ($sitesCollector->sites() as $collectedSites) {
            $siteSynchroniser->storeSites($collectedSites, $this->source, $autoDeployGraceMinutes, $this->runId);

            $collectorLogger->debug('Sites page collected', [
                'site_count' => $collectedSites->count(),
            ]);
        }

        $siteSynchroniser->markUncollectedMissing($this->source, $this->runId);

        $events->dispatch(new SitesCollected($this->source));

        $collectorLogger->info('Sites collector ended');

        Assert::isInstanceOf($this->batch(), Batch::class)
            ->add([new CollectSiteDomainsOfSites($this->runId, $this->source)]);
    }

    public function lockKey(): string
    {
        return $this->source;
    }
}
