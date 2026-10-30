<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Models\Site;
use Webmozart\Assert\Assert;

class InspectSites implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(CollectorLogger $collectorLogger): void
    {
        $collectedSites = Site::query()
            ->collected()
            ->get();

        $inspectSiteJobs = [];

        foreach ($collectedSites as $collectedSite) {
            $inspectSiteJobs[] = new InspectSite($collectedSite);

            $collectorLogger->debug('Site inspection queued', ['site' => $collectedSite->name]);
        }

        Assert::isInstanceOf($this->batch(), Batch::class)->add($inspectSiteJobs);
    }
}
