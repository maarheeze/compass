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
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\SiteInspected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateIncompatibleClient implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(ForgeRunSucceeded|SiteInspected $collectedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        match (true) {
            $collectedEvent instanceof ForgeRunSucceeded => $this->handleForgeRunSucceeded($observedAt),
            $collectedEvent instanceof SiteInspected => $this->handleSiteInspected($collectedEvent, $observedAt),
        };
    }

    private function handleForgeRunSucceeded(CarbonImmutable $observedAt): void
    {
        foreach (Site::query()->whereNotNull('missing_at')->get() as $missingSite) {
            $this->events->dispatch(new ConditionObserved(
                AlertType::INCOMPATIBLE_CLIENT,
                $missingSite,
                ConditionState::RESOLVED,
                $observedAt,
            ));
        }
    }

    private function handleSiteInspected(SiteInspected $siteInspected, CarbonImmutable $observedAt): void
    {
        $site = $siteInspected->site;
        $observedCondition = match ($site->missing_at === null && $site->client_status === ClientStatus::INCOMPATIBLE) {
            true => ConditionState::ACTIVE,
            false => ConditionState::RESOLVED,
        };

        $this->events->dispatch(new ConditionObserved(
            AlertType::INCOMPATIBLE_CLIENT,
            $site,
            $observedCondition,
            $observedAt,
        ));
    }
}
