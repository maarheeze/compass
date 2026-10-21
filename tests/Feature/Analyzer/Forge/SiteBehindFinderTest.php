<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\SiteBehindFinder;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('find sites finds a site that is behind', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
    ]);

    $siteBehindFinder = $this->app->make(SiteBehindFinder::class);
    $sitesBehind = $siteBehindFinder->findSites();

    expect($sitesBehind->sole()->is($site))
        ->toBeTrue();
});

test('find sites leaves out a site that runs the last commit', function (): void {
    $repository = Repository::factory()->create();
    Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => $repository->last_commit_sha,
    ]);

    $siteBehindFinder = $this->app->make(SiteBehindFinder::class);
    $sitesBehind = $siteBehindFinder->findSites();

    expect($sitesBehind)
        ->toBeEmpty();
});
