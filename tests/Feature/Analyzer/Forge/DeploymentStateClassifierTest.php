<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Analyzer\Forge\DeploymentStateClassifier;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('classify is behind when the deployed commit is not the last commit of the repository', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => false,
    ]);

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::BEHIND);
});

test('classify is deploying when auto-deploy is still within its grace period for the last commit', function (): void {
    $autoDeployGraceMinutes = fake()->numberBetween(1, 60);
    $this->setConfiguration('compass.forge.auto_deploy_grace_minutes', $autoDeployGraceMinutes);
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $repository = Repository::factory()->create([
        'last_commit_sha' => fake()->unique()->sha1(),
        'last_commit_committed_at' => $now->subSeconds(fake()->numberBetween(0, $autoDeployGraceMinutes * 60 - 1)),
    ]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => true,
    ]);

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::DEPLOYING);
});

test('classify is deployment overdue when auto-deploy has passed its grace period for the last commit', function (): void {
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

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::DEPLOYMENT_OVERDUE);
});

test('classify is up to date when the deployed commit is the last commit of the repository', function (): void {
    $repository = Repository::factory()->create();
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => $repository->last_commit_sha,
    ]);

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::UP_TO_DATE);
});

test('classify is unknown when the site deploys another branch than the default branch', function (): void {
    $repository = Repository::factory()->create(['default_branch' => fake()->unique()->word()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => fake()->unique()->word(),
        'deployed_commit_hash' => fake()->sha1(),
    ]);

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::UNKNOWN);
});

test('classify is unknown when the site was never deployed', function (): void {
    $repository = Repository::factory()->create();
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => null,
    ]);

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::UNKNOWN);
});

test('classify is unknown when the site has no collected repository', function (): void {
    $site = Site::factory()->create([
        'repository_url' => fake()->url(),
        'deployed_commit_hash' => fake()->sha1(),
    ]);

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::UNKNOWN);
});
