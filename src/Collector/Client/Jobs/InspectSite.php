<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\SerializesModels;
use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Collector\Client\ClientNotInstalled;
use Maarheeze\Compass\Collector\Client\ClientUnauthorized;
use Maarheeze\Compass\Collector\Client\ClientUnreachable;
use Maarheeze\Compass\Collector\Client\SiteQuery;
use Maarheeze\Compass\Collector\Client\SiteReportSynchronizer;
use Maarheeze\Compass\Models\Site;
use Throwable;

class InspectSite implements ShouldQueue
{
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
        SiteQuery $siteQuery,
        SiteReportSynchronizer $siteReportSynchronizer,
    ): void {
        if (!$clientKey->hasKey($this->site)) {
            return;
        }

        try {
            $siteReport = $siteQuery->report($this->site);
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
