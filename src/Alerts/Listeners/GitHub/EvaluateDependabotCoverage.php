<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\GitHub;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Analyzer\Dependabot\DependabotCoverageChecker;
use Maarheeze\Compass\Analyzer\Dependabot\PackageEcosystem;
use Maarheeze\Compass\Collections\ConditionObservedCollection;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\Events\DependabotYmlChanged;
use Maarheeze\Compass\Collector\Events\PackageJsonChanged;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class EvaluateDependabotCoverage implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private DependabotCoverageChecker $dependabotCoverageChecker,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(ComposerJsonChanged|DependabotYmlChanged|PackageJsonChanged $changedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());
        $repository = $changedEvent->repository->load(['composerJson', 'dependabotYml']);

        $conditionObservations = new ConditionObservedCollection();

        foreach (PackageEcosystem::cases() as $packageEcosystem) {
            $conditionObservations->push(new ConditionObserved(
                $this->alertType($packageEcosystem),
                $repository,
                $this->observedCondition($repository, $packageEcosystem),
                $observedAt,
            ));
        }

        $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
    }

    private function alertType(PackageEcosystem $packageEcosystem): AlertType
    {
        return match ($packageEcosystem) {
            PackageEcosystem::COMPOSER => AlertType::DEPENDABOT_COMPOSER_MISSING,
            PackageEcosystem::NPM => AlertType::DEPENDABOT_NPM_MISSING,
        };
    }

    private function observedCondition(Repository $repository, PackageEcosystem $packageEcosystem): ConditionState
    {
        if ($this->dependabotCoverageChecker->isUncovered($repository, $packageEcosystem)) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
