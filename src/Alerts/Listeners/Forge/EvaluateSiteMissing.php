<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteMissing implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
    ) {
    }

    public function handle(SitesCollected|SiteMissingIgnored $collectedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        match (true) {
            $collectedEvent instanceof SitesCollected => $this->handleSitesCollected($observedAt),
            $collectedEvent instanceof SiteMissingIgnored => $this->handleSiteMissingIgnored(
                $collectedEvent,
                $observedAt,
            ),
        };
    }

    private function handleSitesCollected(CarbonImmutable $observedAt): void
    {
        $sites = Site::query()->lazyById();

        foreach ($sites as $site) {
            $this->observe($site, $observedAt);
        }
    }

    private function handleSiteMissingIgnored(SiteMissingIgnored $siteMissingIgnored, CarbonImmutable $observedAt): void
    {
        $this->observe($siteMissingIgnored->site, $observedAt);
    }

    private function observe(Site $site, CarbonImmutable $observedAt): void
    {
        $this->events->dispatch(new ConditionObserved(
            AlertType::SITE_MISSING,
            $site,
            $this->observedCondition($site),
            $observedAt,
        ));
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($site->missingState() === MissingState::MISSING) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
