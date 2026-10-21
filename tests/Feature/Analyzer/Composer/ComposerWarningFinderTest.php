<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\ComposerWarningFinder;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('find repositories finds an active repository with a composer warning', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'is_active' => true,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);

    $composerWarningFinder = $this->app->make(ComposerWarningFinder::class);
    $repositoriesWithComposerWarning = $composerWarningFinder->findRepositories();

    expect($repositoriesWithComposerWarning->sole()->is($repository))
        ->toBeTrue();
});

test('find repositories leaves out a repository without a composer warning', function (): void {
    Repository::factory()->create([
        'composer_json_oid' => null,
        'is_active' => true,
    ]);

    $composerWarningFinder = $this->app->make(ComposerWarningFinder::class);
    $repositoriesWithComposerWarning = $composerWarningFinder->findRepositories();

    expect($repositoriesWithComposerWarning)
        ->toBeEmpty();
});

test('find repositories leaves out an inactive repository', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'is_active' => false,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);

    $composerWarningFinder = $this->app->make(ComposerWarningFinder::class);
    $repositoriesWithComposerWarning = $composerWarningFinder->findRepositories();

    expect($repositoriesWithComposerWarning)
        ->toBeEmpty();
});
