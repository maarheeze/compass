<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\Client;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Collector\SiteReports\CollectedSiteReport;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
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
    public function storeReport(SiteReport $siteReport, CollectedSiteReport $collectedSiteReport): void
    {
        match ($collectedSiteReport->status) {
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::UNAUTHORIZED => $this->storeStatus($siteReport, $collectedSiteReport),
            SiteReportStatus::INSTALLED,
            SiteReportStatus::NOT_INSTALLED => $this->storeReportAndEndpoints($siteReport, $collectedSiteReport),
        };
    }

    /**
     * @throws Throwable
     */
    private function storeReportAndEndpoints(SiteReport $siteReport, CollectedSiteReport $collectedSiteReport): void
    {
        $this->connection->transaction(static function () use ($siteReport, $collectedSiteReport): void {
            $siteReport->fill([
                'status' => $collectedSiteReport->status,
                'app_debug' => $collectedSiteReport->appDebug,
                'app_env' => $collectedSiteReport->appEnv,
                'app_name' => $collectedSiteReport->appName,
                'collected_at' => CarbonImmutable::now(),
            ])->save();

            $droppedSiteEndpoints = $siteReport->endpoints()
                ->whereNotIn('path', $collectedSiteReport->endpoints)
                ->get();

            foreach ($droppedSiteEndpoints as $droppedSiteEndpoint) {
                $droppedSiteEndpoint->delete();
            }

            foreach ($collectedSiteReport->endpoints as $endpoint) {
                $siteReport->endpoints()->firstOrCreate(['path' => $endpoint]);
            }
        });
    }

    private function storeStatus(SiteReport $siteReport, CollectedSiteReport $collectedSiteReport): void
    {
        $siteReport->fill([
            'status' => $collectedSiteReport->status,
            'collected_at' => CarbonImmutable::now(),
        ])->save();
    }
}
