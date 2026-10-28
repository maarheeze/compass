<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Analyzer\Forge\DeploymentStateClassifier;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateOverdueDeployments implements ShouldQueue
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

        foreach (Site::query()->with('repository')->get() as $site) {
            $observedCondition = match ($this->deploymentStateClassifier->classify($site)) {
                DeploymentState::DEPLOYMENT_OVERDUE => AlertCondition::PRESENT,
                default => AlertCondition::GONE,
            };

            $this->events->dispatch(new ConditionObserved(
                AlertType::OVERDUE_DEPLOYMENT,
                $site,
                $observedCondition,
                $observedAt,
            ));
        }
    }
}
