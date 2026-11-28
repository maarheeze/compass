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
            $this->storeScheduledTasks($siteReport, $collectedSiteReport->scheduler);

            if ($collectedSiteReport->endpoints === null) {
                return;
            }

            $this->storeEndpoints($siteReport, $collectedSiteReport->endpoints);
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

    /**
     * @param list<CollectedSiteEndpoint> $collectedSiteEndpoints
     */
    private function storeEndpoints(SiteReport $siteReport, array $collectedSiteEndpoints): void
    {
        $reportedEndpointPaths = array_map(static function (CollectedSiteEndpoint $collectedSiteEndpoint): EndpointPath {
            return $collectedSiteEndpoint->path;
        }, $collectedSiteEndpoints);
        $droppedSiteEndpoints = $siteReport->endpoints()
            ->whereNotIn('path', $reportedEndpointPaths)
            ->get();

        foreach ($droppedSiteEndpoints as $droppedSiteEndpoint) {
            $droppedSiteEndpoint->delete();
        }

        foreach ($collectedSiteEndpoints as $collectedSiteEndpoint) {
            $this->storeEndpoint($siteReport, $collectedSiteEndpoint);
        }
    }

    private function storeScheduledTasks(SiteReport $siteReport, ?CollectedScheduler $collectedScheduler): void
    {
        if ($collectedScheduler === null) {
            return;
        }

        $siteReport->scheduledTasks()->delete();

        foreach ($collectedScheduler->tasks as $collectedScheduledTask) {
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
            'scheduler_reported' => $collectedSiteReport->scheduler !== null,
            'scheduler_grace_minutes' => $schedulerGraceMinutes,
            'collected_at' => $collectedAt,
        ]);

        if ($collectedSiteReport->scheduler !== null) {
            $siteReport->scheduler_last_ran_at = $collectedSiteReport->scheduler->lastRanAt;
        }

        $siteReport->save();
    }
}
