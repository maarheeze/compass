<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Client;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservedCollection;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteClientUnauthorized implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $siteReport = $siteInspected->siteReport;
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservedCollection([
            new ConditionObserved(
                AlertType::SITE_CLIENT_UNAUTHORIZED,
                $siteReport,
                $this->observedCondition($siteReport),
                CarbonImmutable::instance($this->clock->now()),
            ),
        ])));
    }

    private function observedCondition(SiteReport $siteReport): ConditionState
    {
        if ($siteReport->status === SiteReportStatus::UNAUTHORIZED) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
