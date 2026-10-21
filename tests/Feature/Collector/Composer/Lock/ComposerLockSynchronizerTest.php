<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\LockedPackageCollection;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSummary;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSynchronizer;
use Maarheeze\Compass\Collector\Composer\Lock\LockedPackage;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Repository;

test('store stores the composer lock of the repository', function (): void {
    $repository = Repository::factory()->create();
    $oid = fake()->sha1();
    $composerLockSummary = new ComposerLockSummary(
        fake()->numerify('^#.#'),
        new LockedPackageCollection(),
    );

    $composerLockSynchronizer = $this->app->make(ComposerLockSynchronizer::class);
    $composerLockSynchronizer->store($repository, $oid, $composerLockSummary);

    $this->assertDatabaseHas(ComposerLock::class, [
        'repository_id' => $repository->id,
        'oid' => $oid,
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

    $composerLockSynchronizer = $this->app->make(ComposerLockSynchronizer::class);
    $composerLockSynchronizer->store($repository, fake()->sha1(), new ComposerLockSummary(
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
    $composerLock = ComposerLock::factory()->create(['oid' => fake()->unique()->sha1()]);
    $oid = fake()->unique()->sha1();

    $composerLockSynchronizer = $this->app->make(ComposerLockSynchronizer::class);
    $composerLockSynchronizer->store($composerLock->repository, $oid, new ComposerLockSummary(
        fake()->numerify('^#.#'),
        new LockedPackageCollection(),
    ));

    $this->assertDatabaseHas(ComposerLock::class, [
        'id' => $composerLock->id,
        'oid' => $oid,
    ]);
});

test('store removes the packages collected before', function (): void {
    $composerLockPackage = ComposerLockPackage::factory()->create();
    $repository = $composerLockPackage->composerLock->repository;

    $composerLockSynchronizer = $this->app->make(ComposerLockSynchronizer::class);
    $composerLockSynchronizer->store($repository, fake()->sha1(), new ComposerLockSummary(
        fake()->numerify('^#.#'),
        new LockedPackageCollection(),
    ));

    $this->assertDatabaseMissing(ComposerLockPackage::class, ['id' => $composerLockPackage->id]);
});

test('delete removes the composer lock of the repository', function (): void {
    $composerLock = ComposerLock::factory()->create();

    $composerLockSynchronizer = $this->app->make(ComposerLockSynchronizer::class);
    $composerLockSynchronizer->delete($composerLock->repository);

    $this->assertDatabaseMissing(ComposerLock::class, ['id' => $composerLock->id]);
});
