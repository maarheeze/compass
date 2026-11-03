<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Composer;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarning;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningDetector;
use Maarheeze\Compass\Collections\ConditionObservedCollection;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Psr\Clock\ClockInterface;

readonly class EvaluateComposerWarning implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private ComposerWarningDetector $composerWarningDetector,
        private QueueingDispatcher $dispatcher,
    ) {
    }

    public function handle(ComposerJsonChanged|ComposerLockChanged $changedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());
        $repository = $changedEvent->repository->load('composerJson');
        $detectedComposerWarning = $this->composerWarningDetector->detect($repository);

        $conditionObservations = new ConditionObservedCollection();

        foreach (ComposerWarning::cases() as $composerWarning) {
            $conditionObservations->push(new ConditionObserved(
                $this->alertType($composerWarning),
                $repository,
                $this->observedCondition($composerWarning, $detectedComposerWarning),
                $observedAt,
            ));
        }

        $this->dispatcher->dispatch(new HandleConditionObservations($conditionObservations));
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
