<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\SiteBehindFinder;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('find sites finds an active site that is behind', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'is_active' => true,
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
        'is_active' => true,
    ]);

    $siteBehindFinder = $this->app->make(SiteBehindFinder::class);
    $sitesBehind = $siteBehindFinder->findSites();

    expect($sitesBehind)
        ->toBeEmpty();
});

test('find sites leaves out an inactive site', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'is_active' => false,
    ]);

    $siteBehindFinder = $this->app->make(SiteBehindFinder::class);
    $sitesBehind = $siteBehindFinder->findSites();

    expect($sitesBehind)
        ->toBeEmpty();
});
