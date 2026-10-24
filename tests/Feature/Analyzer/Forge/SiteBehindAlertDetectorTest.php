<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\SiteBehindAlertDetector;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('find alerts finds a site that is behind', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
    ]);

    $siteBehindAlertDetector = $this->app->make(SiteBehindAlertDetector::class);
    $alerts = $siteBehindAlertDetector->findAlerts();

    expect($alerts->sole()->site->is($site))
        ->toBeTrue();
});

test('find alerts leaves out a site that runs the last commit', function (): void {
    $repository = Repository::factory()->create();
    Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => $repository->last_commit_sha,
    ]);

    $siteBehindAlertDetector = $this->app->make(SiteBehindAlertDetector::class);
    $alerts = $siteBehindAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
