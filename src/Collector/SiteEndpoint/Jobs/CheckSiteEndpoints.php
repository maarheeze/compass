<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\SiteEndpoint;
use Webmozart\Assert\Assert;

class CheckSiteEndpoints implements LockedJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;

    public function handle(CollectorLogger $collectorLogger): void
    {
        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $collectedSiteEndpointChunks = SiteEndpoint::query()
            ->ofCollectedSites()
            ->with('siteReport.site')
            ->lazyById()
            ->chunk(100);

        foreach ($collectedSiteEndpointChunks as $collectedSiteEndpoints) {
            $checkSiteEndpointJobs = [];

            foreach ($collectedSiteEndpoints as $collectedSiteEndpoint) {
                $checkSiteEndpointJobs[] = new CheckSiteEndpoint($collectedSiteEndpoint);

                $collectorLogger->debug('Endpoint check queued', [
                    'site' => $collectedSiteEndpoint->siteReport->site->name,
                    'path' => $collectedSiteEndpoint->path->toString(),
                ]);
            }

            $batch->add($checkSiteEndpointJobs);
        }
    }

    public function lockKey(): string
    {
        return TaskType::SITE_ENDPOINTS->value;
    }
}
