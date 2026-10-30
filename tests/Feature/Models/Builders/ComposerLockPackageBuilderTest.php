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
