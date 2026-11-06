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
use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Analyzer\Forge\DeploymentStateClassifier;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteDeploymentOverdue implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private DeploymentStateClassifier $deploymentStateClassifier,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(SitesCollected $sitesCollected): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        $siteChunks = Site::query()
            ->ofSource($sitesCollected->source)
            ->with('repository')
            ->lazyById()
            ->chunk(100);

        foreach ($siteChunks as $sites) {
            $conditionObservations = new ConditionObservationCollection();

            foreach ($sites as $site) {
                $conditionObservations->push(new ConditionObservation(
                    AlertType::SITE_DEPLOYMENT_OVERDUE,
                    $site,
                    $this->observedCondition($site),
                    $observedAt,
                ));
            }

            $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
        }
    }

    private function observedCondition(Site $site): ConditionState
    {
        if ($this->deploymentStateClassifier->classify($site) === DeploymentState::DEPLOYMENT_OVERDUE) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
