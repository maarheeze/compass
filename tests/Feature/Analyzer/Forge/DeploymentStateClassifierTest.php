<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Analyzer\Forge\DeploymentStateClassifier;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('classify is behind when the deployed commit is not the last commit of the repository', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
    ]);

    $deploymentStateClassifier = $this->app->make(DeploymentStateClassifier::class);
    $deploymentState = $deploymentStateClassifier->classify($site);

    expect($deploymentState)
        ->toBe(DeploymentState::BEHIND);
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
