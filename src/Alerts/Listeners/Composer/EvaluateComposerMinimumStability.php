<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Composer;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Types\ComposerMinimumStabilityUnstable;
use Maarheeze\Compass\Analyzer\Composer\MinimumStabilityChecker;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class EvaluateComposerMinimumStability implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private QueueingDispatcher $dispatcher,
        private MinimumStabilityChecker $minimumStabilityChecker,
    ) {
    }

    public function handle(ComposerJsonChanged $composerJsonChanged): void
    {
        $repository = $composerJsonChanged->repository->load('composerJson');

        $this->dispatcher->dispatch(new HandleConditionObservations(new ConditionObservationCollection([
            new ConditionObservation(
                new ComposerMinimumStabilityUnstable(),
                $repository,
                $this->observedCondition($repository),
                CarbonImmutable::instance($this->clock->now()),
            ),
        ])));
    }

    private function observedCondition(Repository $repository): ConditionState
    {
        if (!$this->minimumStabilityChecker->isApplicable($repository)) {
            return ConditionState::NOT_APPLICABLE;
        }

        if ($this->minimumStabilityChecker->isStable($repository)) {
            return ConditionState::RESOLVED;
        }

        return ConditionState::ACTIVE;
    }
}
