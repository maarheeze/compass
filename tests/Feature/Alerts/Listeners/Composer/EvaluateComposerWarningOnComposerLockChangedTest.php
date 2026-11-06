<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarningOnComposerLockChanged;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Maarheeze\Compass\Models\Repository;

test('handle observes the changed repository', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $repository = Repository::factory()->create();

    $evaluateComposerWarningOnComposerLockChanged = $this->app->make(EvaluateComposerWarningOnComposerLockChanged::class);
    $evaluateComposerWarningOnComposerLockChanged->handle(new ComposerLockChanged($repository));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($repository): bool {
                return $conditionObservation->alertType === AlertType::COMPOSER_PROJECT_WITHOUT_LOCK
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository);
            });
        },
    );
});
