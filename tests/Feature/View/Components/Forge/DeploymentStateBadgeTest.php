<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\Forge\DeploymentStateBadge;

test('deployment state is the classified deployment state of the site', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => false,
    ]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    expect($deploymentStateBadge->deploymentState)
        ->toBe(DeploymentState::BEHIND);
});

test('badge type is warning when the site is behind', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => false,
    ]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    expect($deploymentStateBadge->badgeType())
        ->toBe(BadgeType::WARNING);
});

test('badge type is info when the site is deploying', function (): void {
    $repository = Repository::factory()->create([
        'last_commit_committed_at' => fake()->dateTimeBetween('+1 minute', '+1 day'),
    ]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => true,
    ]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    expect($deploymentStateBadge->badgeType())
        ->toBe(BadgeType::INFO);
});

test('badge type is danger when the deployment is overdue', function (): void {
    $repository = Repository::factory()->create([
        'last_commit_committed_at' => fake()->dateTimeBetween('-2 years', '-1 year'),
    ]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'auto_deploy' => true,
    ]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    expect($deploymentStateBadge->badgeType())
        ->toBe(BadgeType::DANGER);
});

test('badge type is success when the site is up to date', function (): void {
    $repository = Repository::factory()->create();
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => $repository->last_commit_sha,
    ]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    expect($deploymentStateBadge->badgeType())
        ->toBe(BadgeType::SUCCESS);
});

test('badge type is not given when the deployment state is unknown', function (): void {
    $site = Site::factory()->create(['repository_url' => null]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    $this->expectException(LogicException::class);

    $deploymentStateBadge->badgeType();
});

test('should not render when the deployment state is unknown', function (): void {
    $site = Site::factory()->create(['repository_url' => null]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    expect($deploymentStateBadge->shouldRender())
        ->toBeFalse();
});

test('render renders the deployment state badge view', function (): void {
    $site = Site::factory()->create(['repository_url' => null]);

    $deploymentStateBadge = $this->app->make(DeploymentStateBadge::class, ['site' => $site]);

    expect($deploymentStateBadge->render()->name())
        ->toBe('compass::components.forge.deployment-state-badge');
});
