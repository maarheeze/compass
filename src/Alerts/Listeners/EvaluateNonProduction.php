<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\SiteInspected;
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
        $isInAnotherEnvironment = $site->app_env !== null && $site->app_env !== 'production';
        $observedCondition = match ($site->missing_at === null && $isInAnotherEnvironment) {
            true => AlertCondition::PRESENT,
            false => AlertCondition::GONE,
        };

        $this->events->dispatch(new ConditionObserved(
            AlertType::NON_PRODUCTION,
            $site,
            $observedCondition,
            CarbonImmutable::instance($this->clock->now()),
        ));
    }
}
