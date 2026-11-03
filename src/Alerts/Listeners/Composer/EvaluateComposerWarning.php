<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Composer;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarning;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningDetector;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Psr\Clock\ClockInterface;

readonly class EvaluateComposerWarning implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private ComposerWarningDetector $composerWarningDetector,
        private Dispatcher $events,
    ) {
    }

    public function handle(ComposerJsonChanged|ComposerLockChanged $changedEvent): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());
        $repository = $changedEvent->repository->load('composerJson');
        $detectedComposerWarning = $this->composerWarningDetector->detect($repository);

        foreach (ComposerWarning::cases() as $composerWarning) {
            $this->events->dispatch(new ConditionObserved(
                $this->alertType($composerWarning),
                $repository,
                $this->observedCondition($composerWarning, $detectedComposerWarning),
                $observedAt,
            ));
        }
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
