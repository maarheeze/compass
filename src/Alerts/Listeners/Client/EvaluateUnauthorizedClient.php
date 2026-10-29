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
use Psr\Clock\ClockInterface;

readonly class EvaluateUnauthorizedClient implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(SiteInspected $siteInspected): void
    {
        $site = $siteInspected->site;
        $observedCondition = match ($site->missing_at === null && $site->client_status === ClientStatus::UNAUTHORIZED) {
            true => ConditionState::ACTIVE,
            false => ConditionState::RESOLVED,
        };

        $this->events->dispatch(new ConditionObserved(
            AlertType::UNAUTHORIZED_CLIENT,
            $site,
            $observedCondition,
            CarbonImmutable::instance($this->clock->now()),
        ));
    }
}
