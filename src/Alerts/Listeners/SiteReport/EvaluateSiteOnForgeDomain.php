<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\SiteReport;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\ForgeDomain;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Types\SiteOnForgeDomain;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\SiteReport;
use Psr\Clock\ClockInterface;
use Webmozart\Assert\Assert;

use function parse_url;

use const PHP_URL_HOST;

readonly class EvaluateSiteOnForgeDomain implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
        private ForgeDomain $forgeDomain,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $siteReport = $siteInspected->siteReport;

        if ($siteReport->maintenance_mode === null) {
            return;
        }

        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            new ConditionObservation(
                new SiteOnForgeDomain(),
                $siteReport,
                $this->observedCondition($siteReport),
                CarbonImmutable::instance($this->clock->now()),
            ),
        ])));
    }

    private function observedCondition(SiteReport $siteReport): ConditionState
    {
        if ($siteReport->maintenance_mode) {
            return ConditionState::RESOLVED;
        }

        if ($this->forgeDomain->covers(Assert::string(parse_url($siteReport->site->url, PHP_URL_HOST)))) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
