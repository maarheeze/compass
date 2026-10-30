<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Forge;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Analyzer\Forge\DeploymentStateClassifier;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateOverdueDeployment implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private DeploymentStateClassifier $deploymentStateClassifier,
        private Dispatcher $events,
    ) {
    }

    public function handle(ForgeRunSucceeded $forgeRunSucceeded): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        $sites = Site::query()
            ->with('repository')
            ->lazyById();

        foreach ($sites as $site) {
            $this->events->dispatch(new ConditionObserved(
                AlertType::OVERDUE_DEPLOYMENT,
                $site,
                $this->observedCondition($site),
                $observedAt,
            ));
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
