<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Dependabot\DependabotCoverageChecker;
use Maarheeze\Compass\Analyzer\Dependabot\PackageEcosystem;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;

test('is uncovered is true for composer in a project without a dependabot yml', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);

    $dependabotCoverageChecker = $this->app->make(DependabotCoverageChecker::class);
    $isUncovered = $dependabotCoverageChecker->isUncovered($repository, PackageEcosystem::COMPOSER);

    expect($isUncovered)
        ->toBeTrue();
});

test('is uncovered is true for composer in a project whose dependabot yml leaves it out', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'package_ecosystems' => ['npm'],
    ]);

    $dependabotCoverageChecker = $this->app->make(DependabotCoverageChecker::class);
    $isUncovered = $dependabotCoverageChecker->isUncovered($repository, PackageEcosystem::COMPOSER);

    expect($isUncovered)
        ->toBeTrue();
});

test('is uncovered is false for composer in a project whose dependabot yml lists it', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'package_ecosystems' => ['composer'],
    ]);

    $dependabotCoverageChecker = $this->app->make(DependabotCoverageChecker::class);
    $isUncovered = $dependabotCoverageChecker->isUncovered($repository, PackageEcosystem::COMPOSER);

    expect($isUncovered)
        ->toBeFalse();
});

test('is uncovered is false for composer in a repository that is not a project', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);

    $dependabotCoverageChecker = $this->app->make(DependabotCoverageChecker::class);
    $isUncovered = $dependabotCoverageChecker->isUncovered($repository, PackageEcosystem::COMPOSER);

    expect($isUncovered)
        ->toBeFalse();
});

test('is uncovered is true for npm in a repository with a package json and without a dependabot yml', function (): void {
    $repository = Repository::factory()->create(['package_json_oid' => fake()->sha1()]);

    $dependabotCoverageChecker = $this->app->make(DependabotCoverageChecker::class);
    $isUncovered = $dependabotCoverageChecker->isUncovered($repository, PackageEcosystem::NPM);

    expect($isUncovered)
        ->toBeTrue();
});

test('is uncovered is false for npm in a repository whose dependabot yml lists it', function (): void {
    $repository = Repository::factory()->create(['package_json_oid' => fake()->sha1()]);
    DependabotYml::factory()->create([
        'repository_id' => $repository->id,
        'package_ecosystems' => ['npm'],
    ]);

    $dependabotCoverageChecker = $this->app->make(DependabotCoverageChecker::class);
    $isUncovered = $dependabotCoverageChecker->isUncovered($repository, PackageEcosystem::NPM);

    expect($isUncovered)
        ->toBeFalse();
});

test('is uncovered is false for npm in a repository without a package json', function (): void {
    $repository = Repository::factory()->create(['package_json_oid' => null]);

    $dependabotCoverageChecker = $this->app->make(DependabotCoverageChecker::class);
    $isUncovered = $dependabotCoverageChecker->isUncovered($repository, PackageEcosystem::NPM);

    expect($isUncovered)
        ->toBeFalse();
});
