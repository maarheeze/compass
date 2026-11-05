<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarning;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes the detected composer warning active now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $evaluateComposerWarning = $this->app->make(EvaluateComposerWarning::class);
    $evaluateComposerWarning->handle(fake()->randomElement([
        new ComposerJsonChanged($repository),
        new ComposerLockChanged($repository),
    ]));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
                return $conditionObservation->alertType === AlertType::COMPOSER_PROJECT_WITHOUT_LOCK
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes the composer warnings that are not detected resolved now', function (AlertType $alertType): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $evaluateComposerWarning = $this->app->make(EvaluateComposerWarning::class);
    $evaluateComposerWarning->handle(fake()->randomElement([
        new ComposerJsonChanged($repository),
        new ComposerLockChanged($repository),
    ]));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($alertType, $now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertType, $now, $repository): bool {
                return $conditionObservation->alertType === $alertType
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
})->with([
    AlertType::COMPOSER_LIBRARY_WITH_LOCK,
    AlertType::COMPOSER_TYPE_MISSING,
    AlertType::COMPOSER_TYPE_UNKNOWN,
]);

test('handle observes every composer warning resolved now for a repository without a composer warning', function (AlertType $alertType): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $evaluateComposerWarning = $this->app->make(EvaluateComposerWarning::class);
    $evaluateComposerWarning->handle(fake()->randomElement([
        new ComposerJsonChanged($repository),
        new ComposerLockChanged($repository),
    ]));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($alertType, $now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($alertType, $now, $repository): bool {
                return $conditionObservation->alertType === $alertType
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
})->with([
    AlertType::COMPOSER_LIBRARY_WITH_LOCK,
    AlertType::COMPOSER_TYPE_MISSING,
    AlertType::COMPOSER_PROJECT_WITHOUT_LOCK,
    AlertType::COMPOSER_TYPE_UNKNOWN,
]);
