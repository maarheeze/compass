<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

class InspectSites implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function __construct(
        public readonly string $source,
    ) {
    }

    public function handle(
        CollectorLogger $collectorLogger,
        SiteReportsCollectorManager $siteReportsCollectorManager,
    ): void {
        $siteReportsCollectorManager->collector($this->source);

        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $collectedSiteChunks = Site::query()
            ->collected()
            ->lazyById()
            ->chunk(100);

        foreach ($collectedSiteChunks as $collectedSites) {
            $inspectSiteJobs = [];

            foreach ($collectedSites as $collectedSite) {
                $inspectSiteJobs[] = new InspectSite($collectedSite, $this->source);

                $collectorLogger->debug('Site inspection queued', ['site' => $collectedSite->name]);
            }

            $batch->add($inspectSiteJobs);
        }
    }
}
