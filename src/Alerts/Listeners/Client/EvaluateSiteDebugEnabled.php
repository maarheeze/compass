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
use Maarheeze\Compass\Models\Site;
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
        $site = $siteInspected->site;
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservedCollection([
            new ConditionObserved(
                AlertType::SITE_DEBUG_ENABLED,
                $site,
                $this->observedCondition($site),
                CarbonImmutable::instance($this->clock->now()),
            ),
        ])));
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($site->app_debug === true) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
