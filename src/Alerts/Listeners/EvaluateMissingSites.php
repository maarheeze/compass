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
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateMissingSites implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(ForgeRunSucceeded|SiteMissingIgnored $collectedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        match (true) {
            $collectedEvent instanceof ForgeRunSucceeded => $this->handleForgeRunSucceeded($observedAt),
            $collectedEvent instanceof SiteMissingIgnored => $this->handleSiteMissingIgnored(
                $collectedEvent,
                $observedAt,
            ),
        };
    }

    private function handleForgeRunSucceeded(CarbonImmutable $observedAt): void
    {
        foreach (Site::query()->get() as $site) {
            $this->observe($site, $observedAt);
        }
    }

    private function handleSiteMissingIgnored(SiteMissingIgnored $siteMissingIgnored, CarbonImmutable $observedAt): void
    {
        $this->observe($siteMissingIgnored->site, $observedAt);
    }

    private function observe(Site $site, CarbonImmutable $observedAt): void
    {
        $observedCondition = match ($site->missing_at !== null && $site->missing_ignored_at === null) {
            true => AlertCondition::PRESENT,
            false => AlertCondition::GONE,
        };

        $this->events->dispatch(new ConditionObserved(
            AlertType::MISSING_SITE,
            $site,
            $observedCondition,
            $observedAt,
        ));
    }
}
