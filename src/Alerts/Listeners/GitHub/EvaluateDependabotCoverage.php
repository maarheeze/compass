<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\GitHub;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Analyzer\Dependabot\DependabotCoverageChecker;
use Maarheeze\Compass\Analyzer\Dependabot\PackageEcosystem;
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
        private Dispatcher $events,
    ) {
    }

    public function handle(ComposerJsonChanged|DependabotYmlChanged|PackageJsonChanged $changedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());
        $repository = $changedEvent->repository->load(['composerJson', 'dependabotYml']);

        foreach (PackageEcosystem::cases() as $packageEcosystem) {
            $this->events->dispatch(new ConditionObserved(
                $this->alertType($packageEcosystem),
                $repository,
                $this->observedCondition($repository, $packageEcosystem),
                $observedAt,
            ));
        }
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
