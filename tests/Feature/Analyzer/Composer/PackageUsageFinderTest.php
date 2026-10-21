<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\PackageUsageFinder;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Repository;

test('find holds the locked packages of the projects', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);
    $lockedPackage = ComposerLockPackage::factory()->create([
        'composer_lock_id' => ComposerLock::factory()->create(['repository_id' => $repository->id])->id,
    ]);

    $packageUsageFinder = $this->app->make(PackageUsageFinder::class);
    $packageUsages = $packageUsageFinder->find($lockedPackage->name);

    expect($packageUsages->lockedPackages->sole()->is($lockedPackage))
        ->toBeTrue();
});

test('find leaves out the locked packages of other repositories than projects', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    $lockedPackage = ComposerLockPackage::factory()->create([
        'composer_lock_id' => ComposerLock::factory()->create(['repository_id' => $repository->id])->id,
    ]);

    $packageUsageFinder = $this->app->make(PackageUsageFinder::class);
    $packageUsages = $packageUsageFinder->find($lockedPackage->name);

    expect($packageUsages->lockedPackages)
        ->toBeEmpty();
});

test('find holds the required packages of the libraries', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    $composerJson = ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    $requiredPackage = ComposerJsonPackage::factory()->create(['composer_json_id' => $composerJson->id]);

    $packageUsageFinder = $this->app->make(PackageUsageFinder::class);
    $packageUsages = $packageUsageFinder->find($requiredPackage->name);

    expect($packageUsages->requiredPackages->sole()->is($requiredPackage))
        ->toBeTrue();
});

test('find leaves out the required packages of other repositories than libraries', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    $composerJson = ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);
    $requiredPackage = ComposerJsonPackage::factory()->create(['composer_json_id' => $composerJson->id]);

    $packageUsageFinder = $this->app->make(PackageUsageFinder::class);
    $packageUsages = $packageUsageFinder->find($requiredPackage->name);

    expect($packageUsages->requiredPackages)
        ->toBeEmpty();
});

test('find leaves out other packages', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    $composerJson = ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    ComposerJsonPackage::factory()->create([
        'composer_json_id' => $composerJson->id,
        'name' => fake()->unique()->slug(),
    ]);

    $packageUsageFinder = $this->app->make(PackageUsageFinder::class);
    $packageUsages = $packageUsageFinder->find(fake()->unique()->slug());

    expect($packageUsages->requiredPackages)
        ->toBeEmpty();
});
