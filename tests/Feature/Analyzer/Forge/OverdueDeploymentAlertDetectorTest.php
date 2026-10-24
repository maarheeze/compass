<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Analyzer\Forge\OverdueDeploymentAlertDetector;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('find alerts finds a site whose auto-deployment is overdue', function (): void {
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

    $overdueDeploymentAlertDetector = $this->app->make(OverdueDeploymentAlertDetector::class);
    $alerts = $overdueDeploymentAlertDetector->findAlerts();

    expect($alerts->sole()->site->is($site))
        ->toBeTrue();
});

test('find alerts leaves out a site that is still deploying', function (): void {
    $autoDeployGraceMinutes = fake()->numberBetween(1, 60);
    $this->setConfiguration('compass.forge.auto_deploy_grace_minutes', $autoDeployGraceMinutes);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'last_commit_sha' => fake()->unique()->sha1(),
        'last_commit_committed_at' => $now->subSeconds(fake()->numberBetween(0, $autoDeployGraceMinutes * 60 - 1)),
    ]);
    Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => true,
    ]);

    $overdueDeploymentAlertDetector = $this->app->make(OverdueDeploymentAlertDetector::class);
    $alerts = $overdueDeploymentAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
