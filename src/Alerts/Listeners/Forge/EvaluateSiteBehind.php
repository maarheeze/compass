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
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;

readonly class EvaluateSiteBehind implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private DeploymentStateClassifier $deploymentStateClassifier,
        private Dispatcher $events,
    ) {
    }

    public function handle(GitHubRunSucceeded|ForgeRunSucceeded $runSucceeded): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        foreach (Site::query()->with('repository')->get() as $site) {
            $observedCondition = match ($this->deploymentStateClassifier->classify($site)) {
                DeploymentState::BEHIND => ConditionState::PRESENT,
                default => ConditionState::GONE,
            };

            $this->events->dispatch(new ConditionObserved(
                AlertType::SITE_BEHIND,
                $site,
                $observedCondition,
                $observedAt,
            ));
        }
    }
}
