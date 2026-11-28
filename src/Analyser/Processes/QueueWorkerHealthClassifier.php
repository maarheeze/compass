<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Processes;

use Maarheeze\Compass\Collections\SiteReportCollection;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

readonly class QueueWorkerHealthClassifier
{
    private const string SYNC_QUEUE_DRIVER = 'sync';

    public function classify(Site $site): QueueWorkerHealth
    {
        $siteReportsWithQueueDriver = $this->siteReportsWithQueueDriver($site);

        if ($siteReportsWithQueueDriver->isEmpty()) {
            return QueueWorkerHealth::UNKNOWN;
        }

        if (!$this->queuesJobs($siteReportsWithQueueDriver)) {
            return QueueWorkerHealth::NOT_NEEDED;
        }

        return match ($site->queue_worker_state) {
            null, QueueWorkerState::UNKNOWN => QueueWorkerHealth::UNKNOWN,
            QueueWorkerState::RUNNING, QueueWorkerState::STARTING => QueueWorkerHealth::RUNNING,
            QueueWorkerState::NOT_CONFIGURED,
            QueueWorkerState::STOPPED,
            QueueWorkerState::FAILING => QueueWorkerHealth::NOT_RUNNING,
        };
    }

    private function queuesJobs(SiteReportCollection $siteReportsWithQueueDriver): bool
    {
        return $siteReportsWithQueueDriver->contains(static function (SiteReport $siteReport): bool {
            return $siteReport->queue_driver !== self::SYNC_QUEUE_DRIVER;
        });
    }

    private function siteReportsWithQueueDriver(Site $site): SiteReportCollection
    {
        return $site->reports->filter(static function (SiteReport $siteReport): bool {
            return $siteReport->status === SiteReportStatus::INSTALLED
                && $siteReport->queue_driver !== null;
        });
    }
}
