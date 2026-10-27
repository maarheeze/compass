<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Database\Eloquent\Builder;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Models\SiteEndpoint;

class CheckSiteEndpoints implements ShouldQueue
{
    use Queueable;

    public function handle(CollectorLogger $collectorLogger, Dispatcher $dispatcher): void
    {
        $collectedSiteEndpoints = SiteEndpoint::query()
            ->whereHas('site', static function (Builder $siteQuery): void {
                $siteQuery->whereNull('missing_at');
            })
            ->with('site')
            ->get();

        foreach ($collectedSiteEndpoints as $collectedSiteEndpoint) {
            $dispatcher->dispatch(new CheckSiteEndpoint($collectedSiteEndpoint));

            $collectorLogger->debug('Endpoint check queued', [
                'site' => $collectedSiteEndpoint->site->name,
                'path' => $collectedSiteEndpoint->path,
            ]);
        }
    }
}
