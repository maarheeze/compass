<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteDomains\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Webmozart\Assert\Assert;

class CollectSiteDomainsOfSites implements LockedJob
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

    public function handle(CollectorLogger $collectorLogger): void
    {
        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $siteReturnedByRunChunks = Site::query()
            ->ofSource($this->source)
            ->returnedByRun($this->runId)
            ->lazyById()
            ->chunk(100);

        foreach ($siteReturnedByRunChunks as $sitesReturnedByRun) {
            $collectSiteDomainsJobs = [];

            foreach ($sitesReturnedByRun as $siteReturnedByRun) {
                $collectSiteDomainsJobs[] = new CollectSiteDomains($siteReturnedByRun);

                $collectorLogger->debug('Site domains collection queued', ['site' => $siteReturnedByRun->name]);
            }

            $batch->add($collectSiteDomainsJobs);
        }
    }

    public function lockKey(): string
    {
        return $this->source;
    }
}
