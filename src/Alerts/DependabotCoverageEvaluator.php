<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Analyzer\Dependabot\DependabotCoverageChecker;
use Maarheeze\Compass\Analyzer\Dependabot\PackageEcosystem;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class DependabotCoverageEvaluator
{
    public function __construct(
        private ClockInterface $clock,
        private DependabotCoverageChecker $dependabotCoverageChecker,
    ) {
    }

    public function conditionObservations(Repository $repository): ConditionObservationCollection
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        $conditionObservations = new ConditionObservationCollection();

        foreach (PackageEcosystem::cases() as $packageEcosystem) {
            $conditionObservations->push(new ConditionObservation(
                $this->alertType($packageEcosystem),
                $repository,
                $this->observedCondition($repository, $packageEcosystem),
                $observedAt,
            ));
        }

        return $conditionObservations;
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
