<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\SiteReport;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Types\SiteNotInProduction;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteNotInProduction implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $siteReport = $siteInspected->siteReport;

        if ($siteReport->app_env === null) {
            return;
        }

        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            new ConditionObservation(
                new SiteNotInProduction(),
                $siteReport,
                $this->observedCondition($siteReport),
                CarbonImmutable::instance($this->clock->now()),
            ),
        ])));
    }

    private function observedCondition(SiteReport $siteReport): ConditionState
    {
        if ($siteReport->app_env !== 'production') {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
