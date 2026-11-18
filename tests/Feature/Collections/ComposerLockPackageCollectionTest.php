<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\PackageVersionCount;
use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Models\ComposerLockPackage;

test('version counts count the locked packages per version, lowest version first', function (): void {
    $lockedPackages = new ComposerLockPackageCollection([
        ComposerLockPackage::factory()->make(['version' => 'v1.13.0']),
        ComposerLockPackage::factory()->make(['version' => 'v2.0.0']),
        ComposerLockPackage::factory()->make(['version' => 'v1.13.0']),
        ComposerLockPackage::factory()->make(['version' => 'v1.2.0']),
    ]);

    $versionCounts = $lockedPackages->versionCounts();

    expect($versionCounts->all())
        ->toEqual([
            new PackageVersionCount('v1.2.0', 1),
            new PackageVersionCount('v1.13.0', 2),
            new PackageVersionCount('v2.0.0', 1),
        ]);
});

test('version counts order a version with a v prefix by its number', function (): void {
    $lockedPackages = new ComposerLockPackageCollection([
        ComposerLockPackage::factory()->make(['version' => 'v1.10.0']),
        ComposerLockPackage::factory()->make(['version' => '1.2.0']),
    ]);

    $versionCounts = $lockedPackages->versionCounts();

    expect($versionCounts->all())
        ->toEqual([
            new PackageVersionCount('1.2.0', 1),
            new PackageVersionCount('v1.10.0', 1),
        ]);
});

test('version counts are empty without locked packages', function (): void {
    $lockedPackages = new ComposerLockPackageCollection();

    $versionCounts = $lockedPackages->versionCounts();

    expect($versionCounts)
        ->toBeEmpty();
});
