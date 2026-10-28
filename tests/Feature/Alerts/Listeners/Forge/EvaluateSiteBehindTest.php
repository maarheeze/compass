<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteBehind;
use Maarheeze\Compass\Collector\Events\ForgeRunSucceeded;
use Maarheeze\Compass\Collector\Events\GitHubRunSucceeded;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes a site behind now', function (GitHubRunSucceeded|ForgeRunSucceeded $runSucceeded): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => false,
    ]);

    $evaluateSiteBehind = $this->app->make(EvaluateSiteBehind::class);
    $evaluateSiteBehind->handle($runSucceeded);

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::SITE_BEHIND
            && $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === ConditionState::PRESENT
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([
    new GitHubRunSucceeded(Uuid::generate()),
    new ForgeRunSucceeded(Uuid::generate()),
]);

test('handle observes an up-to-date site no longer behind now', function (GitHubRunSucceeded|ForgeRunSucceeded $runSucceeded): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create();
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => $repository->last_commit_sha,
    ]);

    $evaluateSiteBehind = $this->app->make(EvaluateSiteBehind::class);
    $evaluateSiteBehind->handle($runSucceeded);

    $eventFake->assertDispatched(ConditionObserved::class, static function (ConditionObserved $conditionObserved) use ($now, $site): bool {
        return $conditionObserved->alertType === AlertType::SITE_BEHIND
            && $conditionObserved->subject instanceof Site
            && $conditionObserved->subject->is($site)
            && $conditionObserved->condition === ConditionState::GONE
            && $conditionObserved->observedAt->equalTo($now);
    });
})->with([
    new GitHubRunSucceeded(Uuid::generate()),
    new ForgeRunSucceeded(Uuid::generate()),
]);
