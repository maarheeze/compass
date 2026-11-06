<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarning;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningDetector;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class ComposerWarningEvaluator
{
    public function __construct(
        private ClockInterface $clock,
        private ComposerWarningDetector $composerWarningDetector,
    ) {
    }

    public function conditionObservations(Repository $repository): ConditionObservationCollection
    {
        $repository->load('composerJson');

        $observedAt = CarbonImmutable::instance($this->clock->now());
        $detectedComposerWarning = $this->composerWarningDetector->detect($repository);

        $conditionObservations = new ConditionObservationCollection();

        foreach (ComposerWarning::cases() as $composerWarning) {
            $conditionObservations->push(new ConditionObservation(
                $this->alertType($composerWarning),
                $repository,
                $this->observedCondition($composerWarning, $detectedComposerWarning),
                $observedAt,
            ));
        }

        return $conditionObservations;
    }

    private function alertType(ComposerWarning $composerWarning): AlertType
    {
        return match ($composerWarning) {
            ComposerWarning::LIBRARY_WITH_LOCK => AlertType::COMPOSER_LIBRARY_WITH_LOCK,
            ComposerWarning::MISSING_TYPE => AlertType::COMPOSER_TYPE_MISSING,
            ComposerWarning::PROJECT_WITHOUT_LOCK => AlertType::COMPOSER_PROJECT_WITHOUT_LOCK,
            ComposerWarning::UNKNOWN_TYPE => AlertType::COMPOSER_TYPE_UNKNOWN,
        };
    }

    private function observedCondition(
        ComposerWarning $composerWarning,
        ?ComposerWarning $detectedComposerWarning,
    ): ConditionState {
        if ($composerWarning === $detectedComposerWarning) {
            return ConditionState::ACTIVE;
        }

        return ConditionState::RESOLVED;
    }
}
