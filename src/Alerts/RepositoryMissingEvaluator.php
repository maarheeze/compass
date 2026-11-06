<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class RepositoryMissingEvaluator
{
    public function __construct(
        private ClockInterface $clock,
    ) {
    }

    public function conditionObservation(Repository $repository): ConditionObservation
    {
        return new ConditionObservation(
            AlertType::REPOSITORY_MISSING,
            $repository,
            $this->observedCondition($repository),
            CarbonImmutable::instance($this->clock->now()),
        );
    }

    private function observedCondition(Repository $repository): ConditionState
    {
        if ($repository->missingState() === MissingState::MISSING) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
