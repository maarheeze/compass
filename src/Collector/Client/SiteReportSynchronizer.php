<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Throwable;

readonly class SiteReportSynchronizer
{
    public function __construct(
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function storeReport(Site $site, CollectedSiteReport $collectedSiteReport): void
    {
        match ($collectedSiteReport->status) {
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::UNAUTHORIZED => $this->storeStatus($site, $collectedSiteReport),
            SiteReportStatus::INSTALLED,
            SiteReportStatus::NOT_INSTALLED => $this->storeReportAndEndpoints($site, $collectedSiteReport),
        };
    }

    /**
     * @throws Throwable
     */
    private function storeReportAndEndpoints(Site $site, CollectedSiteReport $collectedSiteReport): void
    {
        $this->connection->transaction(static function () use ($site, $collectedSiteReport): void {
            $site->update([
                'client_status' => $collectedSiteReport->status,
                'app_debug' => $collectedSiteReport->appDebug,
                'app_env' => $collectedSiteReport->appEnv,
                'app_name' => $collectedSiteReport->appName,
                'client_collected_at' => CarbonImmutable::now(),
            ]);

            $droppedSiteEndpoints = $site->endpoints()
                ->whereNotIn('path', $collectedSiteReport->endpoints)
                ->get();

            foreach ($droppedSiteEndpoints as $droppedSiteEndpoint) {
                $droppedSiteEndpoint->delete();
            }

            foreach ($collectedSiteReport->endpoints as $endpoint) {
                $site->endpoints()->firstOrCreate(['path' => $endpoint]);
            }
        });
    }

    private function storeStatus(Site $site, CollectedSiteReport $collectedSiteReport): void
    {
        $site->update([
            'client_status' => $collectedSiteReport->status,
            'client_collected_at' => CarbonImmutable::now(),
        ]);
    }
}
