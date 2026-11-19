<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\ComposerLockPackage;

test('named keeps a package with the name', function (): void {
    $lockedPackage = ComposerLockPackage::factory()->create();

    $lockedPackagesNamed = ComposerLockPackage::query()
        ->named($lockedPackage->name)
        ->get();

    expect($lockedPackagesNamed->sole()->is($lockedPackage))
        ->toBeTrue();
});

test('named leaves out a package with another name', function (): void {
    ComposerLockPackage::factory()->create(['name' => fake()->unique()->slug()]);

    $lockedPackagesNamed = ComposerLockPackage::query()
        ->named(fake()->unique()->slug())
        ->get();

    expect($lockedPackagesNamed)
        ->toBeEmpty();
});

test('versioned keeps a package with the version', function (): void {
    $lockedPackage = ComposerLockPackage::factory()->create();

    $lockedPackagesVersioned = ComposerLockPackage::query()
        ->versioned($lockedPackage->version)
        ->get();

    expect($lockedPackagesVersioned->sole()->is($lockedPackage))
        ->toBeTrue();
});

test('versioned leaves out a package with another version', function (): void {
    ComposerLockPackage::factory()->create(['version' => fake()->unique()->semver()]);

    $lockedPackagesVersioned = ComposerLockPackage::query()
        ->versioned(fake()->unique()->semver())
        ->get();

    expect($lockedPackagesVersioned)
        ->toBeEmpty();
});

test('listed is the packages ordered by name', function (): void {
    $laterLockedPackage = ComposerLockPackage::factory()->create(['name' => sprintf('b%s', fake()->slug())]);
    $earlierLockedPackage = ComposerLockPackage::factory()->create(['name' => sprintf('a%s', fake()->slug())]);

    $listedLockedPackages = ComposerLockPackage::query()->listed();

    expect($listedLockedPackages->modelKeys())
        ->toEqual([$earlierLockedPackage->getKey(), $laterLockedPackage->getKey()]);
});

test('of repository keeps only the packages of the repository', function (): void {
    $lockedPackage = ComposerLockPackage::factory()->create();
    ComposerLockPackage::factory()->create();

    $lockedPackagesOfRepository = ComposerLockPackage::query()
        ->ofRepository($lockedPackage->composerLock->repository)
        ->get();

    expect($lockedPackagesOfRepository->sole()->is($lockedPackage))
        ->toBeTrue();
});
