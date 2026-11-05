<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
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

    private function conditionObservation(Site $site, CarbonImmutable $observedAt): ConditionObservation
    {
        return new ConditionObservation(
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
            $conditionObservations = new ConditionObservationCollection();

            foreach ($sites as $site) {
                $conditionObservations->push($this->conditionObservation($site, $observedAt));
            }

            $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
        }
    }

    private function handleSiteMissingIgnored(SiteMissingIgnored $siteMissingIgnored, CarbonImmutable $observedAt): void
    {
        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            $this->conditionObservation($siteMissingIgnored->site, $observedAt),
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
