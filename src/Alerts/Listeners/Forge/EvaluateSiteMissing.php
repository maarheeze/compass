<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservedCollection;
use Maarheeze\Compass\Collector\Events\SiteMissingIgnored;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteMissing implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
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

    private function conditionObserved(Site $site, CarbonImmutable $observedAt): ConditionObserved
    {
        return new ConditionObserved(
            AlertType::SITE_MISSING,
            $site,
            $this->observedCondition($site),
            $observedAt,
        );
    }

    private function handleSitesCollected(CarbonImmutable $observedAt): void
    {
        $siteChunks = Site::query()
            ->lazyById()
            ->chunk(100);

        foreach ($siteChunks as $sites) {
            $conditionObservations = new ConditionObservedCollection();

            foreach ($sites as $site) {
                $conditionObservations->push($this->conditionObserved($site, $observedAt));
            }

            $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
        }
    }

    private function handleSiteMissingIgnored(SiteMissingIgnored $siteMissingIgnored, CarbonImmutable $observedAt): void
    {
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservedCollection([
            $this->conditionObserved($siteMissingIgnored->site, $observedAt),
        ])));
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($site->missingState() === MissingState::MISSING) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
