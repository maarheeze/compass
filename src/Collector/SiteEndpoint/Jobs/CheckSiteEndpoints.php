<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint\Jobs;

use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Models\SiteEndpoint;
use Webmozart\Assert\Assert;

class CheckSiteEndpoints implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;

    public function handle(CollectorLogger $collectorLogger): void
    {
        $collectedSiteEndpoints = SiteEndpoint::query()
            ->whereHas('site', static function (Builder $siteQuery): void {
                $siteQuery->whereNull('missing_at');
            })
            ->with('site')
            ->get();

        $checkSiteEndpointJobs = [];

        foreach ($collectedSiteEndpoints as $collectedSiteEndpoint) {
            $checkSiteEndpointJobs[] = new CheckSiteEndpoint($collectedSiteEndpoint);

            $collectorLogger->debug('Endpoint check queued', [
                'site' => $collectedSiteEndpoint->site->name,
                'path' => $collectedSiteEndpoint->path,
            ]);
        }

        Assert::isInstanceOf($this->batch(), Batch::class)->add($checkSiteEndpointJobs);
    }
}
