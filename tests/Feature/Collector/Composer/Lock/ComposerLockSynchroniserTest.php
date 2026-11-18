<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\LockedPackageCollection;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSummary;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSynchroniser;
use Maarheeze\Compass\Collector\Composer\Lock\LockedPackage;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Repository;

test('store stores the composer lock of the repository', function (): void {
    $repository = Repository::factory()->create();
    $version = fake()->sha1();
    $composerLockSummary = new ComposerLockSummary(
        fake()->numerify('^#.#'),
        new LockedPackageCollection(),
    );

    $composerLockSynchroniser = $this->app->make(ComposerLockSynchroniser::class);
    $composerLockSynchroniser->store($repository, $version, $composerLockSummary);

    $this->assertDatabaseHas(ComposerLock::class, [
        'repository_id' => $repository->id,
        'version' => $version,
        'php_constraint' => $composerLockSummary->phpConstraint,
    ]);
});

test('store stores the locked packages', function (): void {
    $repository = Repository::factory()->create();
    $lockedPackage = new LockedPackage(
        sprintf('%s/%s', fake()->slug(), fake()->slug()),
        fake()->numerify('v#.#.#'),
        fake()->randomElement(PackageSection::cases()),
    );

    $composerLockSynchroniser = $this->app->make(ComposerLockSynchroniser::class);
    $composerLockSynchroniser->store($repository, fake()->sha1(), new ComposerLockSummary(
        fake()->numerify('^#.#'),
        new LockedPackageCollection([$lockedPackage]),
    ));

    $this->assertDatabaseHas(ComposerLockPackage::class, [
        'composer_lock_id' => ComposerLock::query()->where('repository_id', $repository->id)->sole()->id,
        'name' => $lockedPackage->name,
        'version' => $lockedPackage->version,
        'section' => $lockedPackage->section,
    ]);
});

test('store replaces the composer lock collected before', function (): void {
    $composerLock = ComposerLock::factory()->create(['version' => fake()->unique()->sha1()]);
    $version = fake()->unique()->sha1();

    $composerLockSynchroniser = $this->app->make(ComposerLockSynchroniser::class);
    $composerLockSynchroniser->store($composerLock->repository, $version, new ComposerLockSummary(
        fake()->numerify('^#.#'),
        new LockedPackageCollection(),
    ));

    $this->assertDatabaseHas(ComposerLock::class, [
        'id' => $composerLock->id,
        'version' => $version,
    ]);
});

test('store removes the packages collected before', function (): void {
    $composerLockPackage = ComposerLockPackage::factory()->create();
    $repository = $composerLockPackage->composerLock->repository;

    $composerLockSynchroniser = $this->app->make(ComposerLockSynchroniser::class);
    $composerLockSynchroniser->store($repository, fake()->sha1(), new ComposerLockSummary(
        fake()->numerify('^#.#'),
        new LockedPackageCollection(),
    ));

    $this->assertDatabaseMissing(ComposerLockPackage::class, ['id' => $composerLockPackage->id]);
});

test('delete removes the composer lock of the repository', function (): void {
    $composerLock = ComposerLock::factory()->create();

    $composerLockSynchroniser = $this->app->make(ComposerLockSynchroniser::class);
    $composerLockSynchroniser->delete($composerLock->repository);

    $this->assertDatabaseMissing(ComposerLock::class, ['id' => $composerLock->id]);
});
