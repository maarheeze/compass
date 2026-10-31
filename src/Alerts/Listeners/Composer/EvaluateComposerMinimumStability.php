<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Composer;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Analyzer\Composer\MinimumStabilityChecker;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class EvaluateComposerMinimumStability implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private Dispatcher $events,
        private MinimumStabilityChecker $minimumStabilityChecker,
    ) {
    }

    public function handle(GitHubRunSucceeded $gitHubRunSucceeded): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        $repositories = Repository::query()
            ->with('composerJson')
            ->lazyById();

        foreach ($repositories as $repository) {
            $this->events->dispatch(new ConditionObserved(
                AlertType::COMPOSER_MINIMUM_STABILITY_UNSTABLE,
                $repository,
                $this->observedCondition($repository),
                $observedAt,
            ));
        }
    }

    private function observedCondition(Repository $repository): ConditionState
    {
        if ($this->minimumStabilityChecker->isStable($repository)) {
            return ConditionState::RESOLVED;
        }

        return ConditionState::ACTIVE;
    }
}
