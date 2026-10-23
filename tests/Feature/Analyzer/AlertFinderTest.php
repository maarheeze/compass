<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\AlertFinder;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('find holds the missing repositories', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    expect($alerts->missingRepositories->sole()->is($repository))
        ->toBeTrue();
});

test('find holds the missing sites', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    expect($alerts->missingSites->sole()->is($site))
        ->toBeTrue();
});

test('find holds the repositories with a composer warning', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    expect($alerts->repositoriesWithComposerWarning->sole()->is($repository))
        ->toBeTrue();
});

test('find holds the sites behind', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
    ]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    expect($alerts->sitesBehind->sole()->is($site))
        ->toBeTrue();
});
