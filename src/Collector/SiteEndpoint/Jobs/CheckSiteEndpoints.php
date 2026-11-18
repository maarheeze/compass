<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteEndpoint\Jobs;

use Carbon\CarbonImmutable;
use Illuminate\Bus\Batch;
use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Queue\InteractsWithQueue;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Concerns\RetriesWhileLocked;
use Maarheeze\Compass\Collector\LockedJob;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\SiteEndpoint;
use Psr\Clock\ClockInterface;
use Webmozart\Assert\Assert;

class CheckSiteEndpoints implements LockedJob
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use RetriesWhileLocked;

    public function handle(ClockInterface $clock, CollectorLogger $collectorLogger): void
    {
        $batch = Assert::isInstanceOf($this->batch(), Batch::class);
        $dueSiteEndpointChunks = SiteEndpoint::query()
            ->dueForCheck(CarbonImmutable::instance($clock->now()))
            ->with('siteReport.site')
            ->lazyById()
            ->chunk(100);

        foreach ($dueSiteEndpointChunks as $dueSiteEndpoints) {
            $checkSiteEndpointJobs = [];

            foreach ($dueSiteEndpoints as $dueSiteEndpoint) {
                $checkSiteEndpointJobs[] = new CheckSiteEndpoint($dueSiteEndpoint);

                $collectorLogger->debug('Endpoint check queued', [
                    'site' => $dueSiteEndpoint->siteReport->site->name,
                    'path' => $dueSiteEndpoint->path->toString(),
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
