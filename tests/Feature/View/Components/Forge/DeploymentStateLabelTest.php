<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\Forge\DeploymentStateLabel;

test('deployment state is the classified deployment state of the site', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
    ]);

    $deploymentStateLabel = $this->app->make(DeploymentStateLabel::class, ['site' => $site]);

    expect($deploymentStateLabel->deploymentState)
        ->toBe(DeploymentState::BEHIND);
});
