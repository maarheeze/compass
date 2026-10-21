<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKindClassifier;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('classify is other for a repository without a composer json', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $repositoryKindClassifier = $this->app->make(RepositoryKindClassifier::class);
    $repositoryKind = $repositoryKindClassifier->classify($repository);

    expect($repositoryKind)
        ->toBe(RepositoryKind::OTHER);
});

test('classify is library for a composer json of type library', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);

    $repositoryKindClassifier = $this->app->make(RepositoryKindClassifier::class);
    $repositoryKind = $repositoryKindClassifier->classify($repository);

    expect($repositoryKind)
        ->toBe(RepositoryKind::LIBRARY);
});

test('classify is project for a composer json of type project', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $repositoryKindClassifier = $this->app->make(RepositoryKindClassifier::class);
    $repositoryKind = $repositoryKindClassifier->classify($repository);

    expect($repositoryKind)
        ->toBe(RepositoryKind::PROJECT);
});

test('classify is other for a composer json of another type', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => fake()->word(),
    ]);

    $repositoryKindClassifier = $this->app->make(RepositoryKindClassifier::class);
    $repositoryKind = $repositoryKindClassifier->classify($repository);

    expect($repositoryKind)
        ->toBe(RepositoryKind::OTHER);
});

test('classify is other for a composer json that is not collected yet', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);

    $repositoryKindClassifier = $this->app->make(RepositoryKindClassifier::class);
    $repositoryKind = $repositoryKindClassifier->classify($repository);

    expect($repositoryKind)
        ->toBe(RepositoryKind::OTHER);
});
