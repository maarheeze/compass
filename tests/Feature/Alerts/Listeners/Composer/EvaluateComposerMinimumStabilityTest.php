<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerMinimumStability;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes an unstable minimum stability active now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'minimum_stability' => 'dev',
        'prefer_stable' => false,
    ]);

    $evaluateComposerMinimumStability = $this->app->make(EvaluateComposerMinimumStability::class);
    $evaluateComposerMinimumStability->handle(new ComposerJsonChanged($repository));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $repository): bool {
        return $conditionObserved->alertType === AlertType::COMPOSER_MINIMUM_STABILITY_UNSTABLE
            && $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::ACTIVE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes a stable minimum stability resolved now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $evaluateComposerMinimumStability = $this->app->make(EvaluateComposerMinimumStability::class);
    $evaluateComposerMinimumStability->handle(new ComposerJsonChanged($repository));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $repository): bool {
        return $conditionObserved->alertType === AlertType::COMPOSER_MINIMUM_STABILITY_UNSTABLE
            && $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
});
