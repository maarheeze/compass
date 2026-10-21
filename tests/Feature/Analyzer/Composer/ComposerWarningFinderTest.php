<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\ComposerWarningFinder;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('find repositories finds a repository with a composer warning', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
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
    Repository::factory()->create(['composer_json_oid' => null]);

    $composerWarningFinder = $this->app->make(ComposerWarningFinder::class);
    $repositoriesWithComposerWarning = $composerWarningFinder->findRepositories();

    expect($repositoriesWithComposerWarning)
        ->toBeEmpty();
});
