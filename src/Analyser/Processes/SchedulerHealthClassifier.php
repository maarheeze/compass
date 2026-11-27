<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Analyser\Processes;

use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
use Webmozart\Assert\Assert;

readonly class SchedulerHealthClassifier
{
    public function classify(SiteReport $siteReport): SchedulerHealth
    {
        if ($siteReport->status !== SiteReportStatus::INSTALLED) {
            return SchedulerHealth::UNKNOWN;
        }

        if ($siteReport->scheduledTasks->isEmpty()) {
            return SchedulerHealth::NO_TASKS;
        }

        if ($siteReport->scheduler_last_ran_at === null) {
            return SchedulerHealth::NOT_RUNNING;
        }

        $graceEndsAt = $siteReport->scheduler_last_ran_at->addMinutes(
            Assert::integer($siteReport->scheduler_grace_minutes),
        );

        if ($siteReport->collected_at->isAfter($graceEndsAt)) {
            return SchedulerHealth::NOT_RUNNING;
        }

        return SchedulerHealth::RUNNING;
    }
}
