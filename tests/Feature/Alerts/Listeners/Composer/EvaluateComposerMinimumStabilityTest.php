<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerMinimumStability;
use Maarheeze\Compass\Alerts\Types\ComposerMinimumStabilityUnstable;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes an unstable minimum stability active now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'minimum_stability' => 'dev',
        'prefer_stable' => false,
    ]);

    $evaluateComposerMinimumStability = $this->app->make(EvaluateComposerMinimumStability::class);
    $evaluateComposerMinimumStability->handle(new ComposerJsonChanged($repository));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
                return $conditionObservation->alertType instanceof ComposerMinimumStabilityUnstable
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes a stable minimum stability resolved now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'minimum_stability' => 'stable',
    ]);

    $evaluateComposerMinimumStability = $this->app->make(EvaluateComposerMinimumStability::class);
    $evaluateComposerMinimumStability->handle(new ComposerJsonChanged($repository));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
                return $conditionObservation->alertType instanceof ComposerMinimumStabilityUnstable
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes the minimum stability of a repository without a composer json not applicable now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_version' => null]);

    $evaluateComposerMinimumStability = $this->app->make(EvaluateComposerMinimumStability::class);
    $evaluateComposerMinimumStability->handle(new ComposerJsonChanged($repository));

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $repository): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $repository): bool {
                return $conditionObservation->alertType instanceof ComposerMinimumStabilityUnstable
                    && $conditionObservation->subject instanceof Repository
                    && $conditionObservation->subject->is($repository)
                    && $conditionObservation->condition === ConditionState::NOT_APPLICABLE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});
