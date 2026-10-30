<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Client;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteClientIncompatible implements ShouldQueue
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
            AlertType::SITE_CLIENT_INCOMPATIBLE,
            $site,
            $this->observedCondition($site),
            CarbonImmutable::instance($this->clock->now()),
        ));
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($site->client_status === ClientStatus::INCOMPATIBLE) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
