<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Collector\SiteReports;

use Carbon\CarbonImmutable;
use Illuminate\Database\ConnectionInterface;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointPath;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Throwable;

use function array_map;

readonly class SiteReportSynchroniser
{
    public function __construct(
        private ClockInterface $clock,
        private ConnectionInterface $connection,
    ) {
    }

    /**
     * @throws Throwable
     */
    public function storeReport(
        SiteReport $siteReport,
        CollectedSiteReport $collectedSiteReport,
        int $schedulerGraceMinutes,
    ): void {
        match ($collectedSiteReport->status) {
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::UNAUTHORISED => $this->storeStatus(
                $siteReport,
                $collectedSiteReport,
                $schedulerGraceMinutes,
            ),
            SiteReportStatus::INSTALLED,
            SiteReportStatus::NOT_INSTALLED => $this->storeReportAndEndpoints(
                $siteReport,
                $collectedSiteReport,
                $schedulerGraceMinutes,
            ),
        };
    }

    /**
     * @throws Throwable
     */
    private function storeReportAndEndpoints(
        SiteReport $siteReport,
        CollectedSiteReport $collectedSiteReport,
        int $schedulerGraceMinutes,
    ): void {
        $this->connection->transaction(function () use (
            $siteReport,
            $collectedSiteReport,
            $schedulerGraceMinutes,
        ): void {
            $this->storeStatus($siteReport, $collectedSiteReport, $schedulerGraceMinutes);
            $this->storeScheduledTasks($siteReport, $collectedSiteReport);

            $reportedEndpointPaths = array_map(static function (CollectedSiteEndpoint $collectedSiteEndpoint): EndpointPath {
                return $collectedSiteEndpoint->path;
            }, $collectedSiteReport->endpoints);
            $droppedSiteEndpoints = $siteReport->endpoints()
                ->whereNotIn('path', $reportedEndpointPaths)
                ->get();

            foreach ($droppedSiteEndpoints as $droppedSiteEndpoint) {
                $droppedSiteEndpoint->delete();
            }

            foreach ($collectedSiteReport->endpoints as $collectedSiteEndpoint) {
                $this->storeEndpoint($siteReport, $collectedSiteEndpoint);
            }
        });
    }

    private function storeEndpoint(SiteReport $siteReport, CollectedSiteEndpoint $collectedSiteEndpoint): void
    {
        $siteEndpoint = $siteReport->endpoints()->firstOrNew(['path' => $collectedSiteEndpoint->path]);
        $siteEndpoint->frequency_minutes = $collectedSiteEndpoint->frequencyMinutes;

        if ($siteEndpoint->isDirty('frequency_minutes')) {
            $siteEndpoint->due_at = CarbonImmutable::instance($this->clock->now());
        }

        $siteEndpoint->save();
    }

    private function storeScheduledTasks(SiteReport $siteReport, CollectedSiteReport $collectedSiteReport): void
    {
        $siteReport->scheduledTasks()->delete();

        foreach ($collectedSiteReport->scheduledTasks as $collectedScheduledTask) {
            $siteReport->scheduledTasks()->create([
                'command' => $collectedScheduledTask->command,
                'expression' => $collectedScheduledTask->expression,
            ]);
        }
    }

    private function storeStatus(
        SiteReport $siteReport,
        CollectedSiteReport $collectedSiteReport,
        int $schedulerGraceMinutes,
    ): void {
        $collectedAt = CarbonImmutable::instance($this->clock->now());

        $siteReport->fill([
            'status' => $collectedSiteReport->status,
            'app_debug' => $collectedSiteReport->appDebug,
            'app_env' => $collectedSiteReport->appEnv,
            'app_name' => $collectedSiteReport->appName,
            'maintenance_mode' => $collectedSiteReport->maintenanceMode,
            'queue_driver' => $collectedSiteReport->queueDriver,
            'scheduler_last_ran_at' => $collectedSiteReport->schedulerLastRanAt,
            'scheduler_grace_minutes' => $schedulerGraceMinutes,
            'collected_at' => $collectedAt,
        ]);
        $siteReport->save();
    }
}
