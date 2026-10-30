<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Client;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateNonProduction implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $site = $siteInspected->site;
        $this->events->dispatch(new ConditionObserved(
            AlertType::NON_PRODUCTION,
            $site,
            $this->observedCondition($site),
            CarbonImmutable::instance($this->clock->now()),
        ));
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($site->app_env !== null && $site->app_env !== 'production') {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
