<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\SiteBehindEvaluator;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('conditionObservation observes a site behind now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => false,
    ]);

    $siteBehindEvaluator = $this->app->make(SiteBehindEvaluator::class);
    $conditionObservation = $siteBehindEvaluator->conditionObservation($site);

    expect($conditionObservation->alertType)
        ->toBeInstanceOf(SiteBehind::class)
        ->and($conditionObservation->subject)
        ->toBe($site)
        ->and($conditionObservation->condition)
        ->toBe(ConditionState::ACTIVE)
        ->and($conditionObservation->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes an up-to-date site no longer behind now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create();
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => $repository->last_commit_sha,
    ]);

    $siteBehindEvaluator = $this->app->make(SiteBehindEvaluator::class);
    $conditionObservation = $siteBehindEvaluator->conditionObservation($site);

    expect($conditionObservation->alertType)
        ->toBeInstanceOf(SiteBehind::class)
        ->and($conditionObservation->subject)
        ->toBe($site)
        ->and($conditionObservation->condition)
        ->toBe(ConditionState::RESOLVED)
        ->and($conditionObservation->observedAt->equalTo($now))
        ->toBeTrue();
});

test('conditionObservation observes a site deploying another branch not applicable now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['default_branch' => fake()->unique()->slug()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => fake()->unique()->slug(),
    ]);

    $siteBehindEvaluator = $this->app->make(SiteBehindEvaluator::class);
    $conditionObservation = $siteBehindEvaluator->conditionObservation($site);

    expect($conditionObservation->alertType)
        ->toBeInstanceOf(SiteBehind::class)
        ->and($conditionObservation->subject)
        ->toBe($site)
        ->and($conditionObservation->condition)
        ->toBe(ConditionState::NOT_APPLICABLE)
        ->and($conditionObservation->observedAt->equalTo($now))
        ->toBeTrue();
});
