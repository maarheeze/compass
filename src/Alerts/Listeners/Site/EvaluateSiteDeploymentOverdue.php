<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Site;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Types\SiteDeploymentOverdue;
use Maarheeze\Compass\Analyzer\Deployment\DeploymentState;
use Maarheeze\Compass\Analyzer\Deployment\DeploymentStateClassifier;
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
                    new SiteDeploymentOverdue(),
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
        return match ($this->deploymentStateClassifier->classify($site)) {
            DeploymentState::DEPLOYMENT_OVERDUE => ConditionState::ACTIVE,
            DeploymentState::UNKNOWN => ConditionState::NOT_APPLICABLE,
            DeploymentState::BEHIND,
            DeploymentState::DEPLOYING,
            DeploymentState::UP_TO_DATE => ConditionState::RESOLVED,
        };
    }
}
