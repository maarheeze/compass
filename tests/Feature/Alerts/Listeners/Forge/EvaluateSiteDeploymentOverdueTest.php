<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Alerts\Listeners\Forge\EvaluateSiteDeploymentOverdue;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle observes an overdue deployment now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $autoDeployGraceMinutes = fake()->numberBetween(1, 60);
    $this->setConfiguration('compass.forge.auto_deploy_grace_minutes', $autoDeployGraceMinutes);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'last_commit_sha' => fake()->unique()->sha1(),
        'last_commit_committed_at' => $now->subSeconds(fake()->numberBetween($autoDeployGraceMinutes * 60, 86400)),
    ]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => true,
    ]);

    $evaluateOverdueDeployment = $this->app->make(EvaluateSiteDeploymentOverdue::class);
    $evaluateOverdueDeployment->handle(new SitesCollected());

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $site): bool {
                return $conditionObservation->alertType === AlertType::SITE_DEPLOYMENT_OVERDUE
                    && $conditionObservation->subject instanceof Site
                    && $conditionObservation->subject->is($site)
                    && $conditionObservation->condition === ConditionState::ACTIVE
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});

test('handle observes an up-to-date site without an overdue deployment now', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(QueueingDispatcher::class, $busFake);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create();
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => $repository->last_commit_sha,
    ]);

    $evaluateOverdueDeployment = $this->app->make(EvaluateSiteDeploymentOverdue::class);
    $evaluateOverdueDeployment->handle(new SitesCollected());

    $busFake->assertDispatched(
        HandleConditionObservations::class,
        static function (HandleConditionObservations $handleConditionObservations) use ($now, $site): bool {
            return $handleConditionObservations->conditionObservations->contains(static function (ConditionObservation $conditionObservation) use ($now, $site): bool {
                return $conditionObservation->alertType === AlertType::SITE_DEPLOYMENT_OVERDUE
                    && $conditionObservation->subject instanceof Site
                    && $conditionObservation->subject->is($site)
                    && $conditionObservation->condition === ConditionState::RESOLVED
                    && $conditionObservation->observedAt->equalTo($now);
            });
        },
    );
});
