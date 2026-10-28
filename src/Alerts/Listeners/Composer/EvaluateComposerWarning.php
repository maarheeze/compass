<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts\Listeners\Composer;

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Contracts\Queue\ShouldQueue;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
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
            $observedCondition = match ($this->composerWarningDetector->detect($repository) !== null) {
                true => ConditionState::ACTIVE,
                false => ConditionState::RESOLVED,
            };

            $this->events->dispatch(new ConditionObserved(
                AlertType::COMPOSER_WARNING,
                $repository,
                $observedCondition,
                $observedAt,
            ));
        }
    }
}
