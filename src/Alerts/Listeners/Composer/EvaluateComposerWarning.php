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
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;

readonly class EvaluateComposerWarning implements ShouldQueue
{
    public function __construct(
        private ClockInterface $clock,
        private ComposerWarningDetector $composerWarningDetector,
        private Dispatcher $events,
    ) {
    }

    public function handle(GitHubRunSucceeded $gitHubRunSucceeded): void
    {
        $observedAt = CarbonImmutable::instance($this->clock->now());

        foreach (Repository::query()->with('composerJson')->get() as $repository) {
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
    }

    private function alertType(ComposerWarning $composerWarning): AlertType
    {
        return match ($composerWarning) {
            ComposerWarning::LIBRARY_WITH_LOCK => AlertType::LIBRARY_WITH_LOCK,
            ComposerWarning::MISSING_TYPE => AlertType::MISSING_COMPOSER_TYPE,
            ComposerWarning::PROJECT_WITHOUT_LOCK => AlertType::PROJECT_WITHOUT_LOCK,
            ComposerWarning::UNKNOWN_TYPE => AlertType::UNKNOWN_COMPOSER_TYPE,
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
