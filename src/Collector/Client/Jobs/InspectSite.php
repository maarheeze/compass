<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Collector\Client\SiteReportSynchronizer;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\CollectorLogger;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportsCollectorManager;
use Maarheeze\Compass\Models\Site;
use Throwable;

class InspectSite implements ShouldQueue
{
    use Batchable;
    use InteractsWithQueue;
    use Queueable;
    use SerializesModels;

    public function __construct(
        public readonly Site $site,
        public readonly string $sourceKey,
    ) {
    }

    /**
     * @throws CollectingFailed
     * @throws Throwable
     */
    public function handle(
        CollectorLogger $collectorLogger,
        Context $context,
        Dispatcher $events,
        SiteReportsCollectorManager $siteReportsCollectorManager,
        SiteReportSynchronizer $siteReportSynchronizer,
    ): void {
        $siteReportsCollector = $siteReportsCollectorManager->collector($this->sourceKey);

        $context->add('site', $this->site->name);

        if ($this->site->missingState() !== MissingState::COLLECTED) {
            $collectorLogger->debug('Site inspection skipped for a missing site');

            return;
        }

        $siteReportSynchronizer->storeReport($this->site, $siteReportsCollector->siteReport($this->site));

        $collectorLogger->debug('Site inspected', ['client_status' => $this->site->client_status?->value]);

        $events->dispatch(new SiteInspected($this->site));
    }
}
