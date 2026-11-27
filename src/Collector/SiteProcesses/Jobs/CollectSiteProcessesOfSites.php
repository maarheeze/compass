<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteProcesses\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Collector\SiteProcesses\SiteProcessesCollector;
use Maarheeze\Compass\Collector\SourceDrivers;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

class CollectSiteProcessesOfSites implements LockedJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;

    public function __construct(
        private readonly string $source,
    ) {
    }

    public function handle(
        CollectorLogger $collectorLogger,
        SourceDrivers $sourceDrivers,
    ): void {
        $sourceDrivers->collector($this->source, SiteProcessesCollector::class);

        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $collectedSiteChunks = Site::query()
            ->ofSource($this->source)
            ->collected()
            ->lazyById()
            ->chunk(100);

        foreach ($collectedSiteChunks as $collectedSites) {
            $collectSiteProcessesJobs = [];

            foreach ($collectedSites as $collectedSite) {
                $collectSiteProcessesJobs[] = new CollectSiteProcesses($collectedSite);

                $collectorLogger->debug('Site processes collection queued', ['site' => $collectedSite->name]);
            }

            $batch->add($collectSiteProcessesJobs);
        }
    }

    public function lockKey(): string
    {
        return $this->source;
    }
}
