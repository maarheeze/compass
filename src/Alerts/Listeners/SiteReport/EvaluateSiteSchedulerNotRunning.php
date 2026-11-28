<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\SiteReport;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Types\SiteSchedulerNotRunning;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealth;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealthClassifier;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteSchedulerNotRunning implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
        private SchedulerHealthClassifier $schedulerHealthClassifier,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $siteReport = $siteInspected->siteReport;
        $observedCondition = $this->observedCondition($this->schedulerHealthClassifier->classify($siteReport));

        if ($observedCondition === null) {
            return;
        }

        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            new ConditionObservation(
                new SiteSchedulerNotRunning(),
                $siteReport,
                $observedCondition,
                CarbonImmutable::instance($this->clock->now()),
            ),
        ])));
    }

    private function observedCondition(SchedulerHealth $schedulerHealth): ?ConditionState
    {
        return match ($schedulerHealth) {
            SchedulerHealth::NOT_RUNNING => ConditionState::ACTIVE,
            SchedulerHealth::NO_TASKS => ConditionState::NOT_APPLICABLE,
            SchedulerHealth::RUNNING => ConditionState::RESOLVED,
            SchedulerHealth::UNKNOWN => null,
        };
    }
}
