<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Client;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteDebugEnabled implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $siteReport = $siteInspected->siteReport;
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            new ConditionObservation(
                AlertType::SITE_DEBUG_ENABLED,
                $siteReport,
                $this->observedCondition($siteReport),
                CarbonImmutable::instance($this->clock->now()),
            ),
        ])));
    }

    private function observedCondition(SiteReport $siteReport): ConditionState
    {
        if ($siteReport->app_debug === true) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
