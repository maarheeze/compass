<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\SiteMissingEvaluator;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservation observes a missing, not ignored site missing now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $siteMissingEvaluator = $this->app->make(SiteMissingEvaluator::class);
    $conditionObservation = $siteMissingEvaluator->conditionObservation($site);

    expect($conditionObservation->alertType)
        ->toBe(AlertType::SITE_MISSING)
        ->and($conditionObservation->subject)
        ->toBe($site)
        ->and($conditionObservation->condition)
        ->toBe(ConditionState::ACTIVE)
        ->and($conditionObservation->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes a collected site no longer missing now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $site = Site::factory()->create([
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);

    $siteMissingEvaluator = $this->app->make(SiteMissingEvaluator::class);
    $conditionObservation = $siteMissingEvaluator->conditionObservation($site);

    expect($conditionObservation->alertType)
        ->toBe(AlertType::SITE_MISSING)
        ->and($conditionObservation->subject)
        ->toBe($site)
        ->and($conditionObservation->condition)
        ->toBe(ConditionState::RESOLVED)
        ->and($conditionObservation->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes a missing, ignored site no longer missing', function (): void {
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'missing_ignored_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $siteMissingEvaluator = $this->app->make(SiteMissingEvaluator::class);
    $conditionObservation = $siteMissingEvaluator->conditionObservation($site);

    expect($conditionObservation->condition)
        ->toBe(ConditionState::RESOLVED);
});
