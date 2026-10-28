<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\SiteEndpointChecked;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateDownSiteEndpoint implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(ForgeRunSucceeded|SiteEndpointChecked $collectedEvent): void
    {
        match (true) {
            $collectedEvent instanceof ForgeRunSucceeded => $this->handleForgeRunSucceeded(),
            $collectedEvent instanceof SiteEndpointChecked => $this->handleSiteEndpointChecked($collectedEvent),
        };
    }

    private function handleForgeRunSucceeded(): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());
        $missingSites = Site::query()
            ->whereNotNull('missing_at')
            ->with('endpoints')
            ->get();

        foreach ($missingSites as $missingSite) {
            foreach ($missingSite->endpoints as $siteEndpoint) {
                $this->events->dispatch(new ConditionObserved(
                    AlertType::DOWN_SITE_ENDPOINT,
                    $siteEndpoint,
                    AlertCondition::GONE,
                    $observedAt,
                ));
            }
        }
    }

    private function handleSiteEndpointChecked(SiteEndpointChecked $siteEndpointChecked): void
    {
        $siteEndpointCheck = $siteEndpointChecked->siteEndpointCheck;
        $observedCondition = match ($siteEndpointCheck->is_successful) {
            true => AlertCondition::GONE,
            false => AlertCondition::PRESENT,
        };

        $this->events->dispatch(new ConditionObserved(
            AlertType::DOWN_SITE_ENDPOINT,
            $siteEndpointChecked->siteEndpoint,
            $observedCondition,
            $siteEndpointCheck->created_at,
        ));
    }
}
