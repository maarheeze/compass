<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\ComposerWarning;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningDetector;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('detect finds nothing for a repository without a composer json', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBeNull();
});

test('detect finds nothing for a composer json that is not collected yet', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBeNull();
});

test('detect finds a missing type', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBe(ComposerWarning::MISSING_TYPE);
});

test('detect finds a type other than library or project', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => fake()->word(),
    ]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBe(ComposerWarning::UNKNOWN_TYPE);
});

test('detect finds a library with a committed composer lock', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => fake()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBe(ComposerWarning::LIBRARY_WITH_LOCK);
});

test('detect finds nothing for a library without a composer lock', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBeNull();
});

test('detect finds a project without a committed composer lock', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => null,
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBe(ComposerWarning::PROJECT_WITHOUT_LOCK);
});

test('detect finds nothing for a project with a composer lock', function (): void {
    $repository = Repository::factory()->create([
        'composer_json_oid' => fake()->sha1(),
        'composer_lock_oid' => fake()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $composerWarningDetector = $this->app->make(ComposerWarningDetector::class);
    $composerWarning = $composerWarningDetector->detect($repository);

    expect($composerWarning)
        ->toBeNull();
});
