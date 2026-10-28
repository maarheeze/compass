<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Composer\EvaluateComposerWarning;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes the detected composer warning active now', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
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
    $evaluateComposerWarning->handle(new GitHubRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $repository): bool {
        return $conditionObserved->alertType === AlertType::PROJECT_WITHOUT_LOCK
            && $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::ACTIVE
            && $conditionObserved->observedAt->equalTo($now);
    });
});

test('handle observes the composer warnings that are not detected resolved now', function (AlertType $alertType): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
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
    $evaluateComposerWarning->handle(new GitHubRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($alertType, $now, $repository): bool {
        return $conditionObserved->alertType === $alertType
            && $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([
    AlertType::LIBRARY_WITH_LOCK,
    AlertType::MISSING_COMPOSER_TYPE,
    AlertType::UNKNOWN_COMPOSER_TYPE,
]);

test('handle observes every composer warning resolved now for a repository without a composer warning', function (AlertType $alertType): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $evaluateComposerWarning = $this->app->make(EvaluateComposerWarning::class);
    $evaluateComposerWarning->handle(new GitHubRunSucceeded(Uuid::generate()));

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($alertType, $now, $repository): bool {
        return $conditionObserved->alertType === $alertType
            && $conditionObserved->subject instanceof Repository
            && $conditionObserved->subject->is($repository)
            && $conditionObserved->condition === ConditionState::RESOLVED
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([
    AlertType::LIBRARY_WITH_LOCK,
    AlertType::MISSING_COMPOSER_TYPE,
    AlertType::PROJECT_WITHOUT_LOCK,
    AlertType::UNKNOWN_COMPOSER_TYPE,
]);
