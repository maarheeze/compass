<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\AlertFinder;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('find holds the repositories with a composer warning', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'is_active' => true,
    ]);
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
        'is_active' => true,
    ]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    expect($alerts->sitesBehind->sole()->is($site))
        ->toBeTrue();
});
