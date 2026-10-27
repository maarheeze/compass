<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client\Jobs;

use Illuminate\Bus\Batchable;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Collector\Client\ClientIncompatible;
use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Collector\Client\ClientNotInstalled;
use Maarheeze\Compass\Collector\Client\ClientUnauthorized;
use Maarheeze\Compass\Collector\Client\ClientUnreachable;
use Maarheeze\Compass\Collector\Client\SiteQuery;
use Maarheeze\Compass\Collector\Client\SiteReportSynchronizer;
use Maarheeze\Compass\Collector\CollectorLogger;
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
    ) {
    }

    /**
     * @throws Throwable
     */
    public function handle(
        ClientKey $clientKey,
        CollectorLogger $collectorLogger,
        Context $context,
        SiteQuery $siteQuery,
        SiteReportSynchronizer $siteReportSynchronizer,
    ): void {
        $context->add('site', $this->site->name);

        if (!$clientKey->hasKey($this->site)) {
            $collectorLogger->debug('Site inspection skipped without a client key');

            return;
        }

        $this->inspect($siteQuery, $siteReportSynchronizer);

        $collectorLogger->debug('Site inspected', ['client_status' => $this->site->client_status?->value]);
    }

    /**
     * @throws Throwable
     */
    private function inspect(SiteQuery $siteQuery, SiteReportSynchronizer $siteReportSynchronizer): void
    {
        try {
            $siteReport = $siteQuery->report($this->site);
        } catch (ClientIncompatible) {
            $siteReportSynchronizer->markIncompatible($this->site);

            return;
        } catch (ClientNotInstalled) {
            $siteReportSynchronizer->clearReport($this->site);

            return;
        } catch (ClientUnauthorized) {
            $siteReportSynchronizer->markUnauthorized($this->site);

            return;
        } catch (ClientUnreachable) {
            $siteReportSynchronizer->markUnreachable($this->site);

            return;
        }

        $siteReportSynchronizer->storeReport($this->site, $siteReport);
    }
}
