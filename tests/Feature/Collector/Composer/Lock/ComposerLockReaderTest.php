<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\LockedPackageCollection;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockReader;
use Maarheeze\Compass\Collector\Composer\Lock\LockedPackage;
use Maarheeze\Compass\Collector\Composer\PackageSection;

test('php constraint is the php version of the platform', function (): void {
    $phpConstraint = fake()->numerify('^#.#');

    $composerLockReader = $this->app->make(ComposerLockReader::class);
    $composerLockSummary = $composerLockReader->read(json_encode([
        'packages' => [],
        'packages-dev' => [],
        'platform' => [
            'php' => $phpConstraint,
        ],
    ], JSON_THROW_ON_ERROR));

    expect($composerLockSummary->phpConstraint)
        ->toBe($phpConstraint);
});

test('php constraint is null when the platform has no php version', function (): void {
    $composerLockReader = $this->app->make(ComposerLockReader::class);
    $composerLockSummary = $composerLockReader->read(json_encode([
        'packages' => [],
        'packages-dev' => [],
        'platform' => [],
    ], JSON_THROW_ON_ERROR));

    expect($composerLockSummary->phpConstraint)
        ->toBeNull();
});

test('locked packages are the packages and dev packages with their section', function (): void {
    $productionPackage = new LockedPackage(
        sprintf('%s/%s', fake()->slug(), fake()->slug()),
        fake()->numerify('v#.#.#'),
        PackageSection::PRODUCTION,
    );
    $developmentPackage = new LockedPackage(
        sprintf('%s/%s', fake()->slug(), fake()->slug()),
        fake()->numerify('v#.#.#'),
        PackageSection::DEVELOPMENT,
    );

    $composerLockReader = $this->app->make(ComposerLockReader::class);
    $composerLockSummary = $composerLockReader->read(json_encode([
        'packages' => [
            [
                'name' => $productionPackage->name,
                'version' => $productionPackage->version,
            ],
        ],
        'packages-dev' => [
            [
                'name' => $developmentPackage->name,
                'version' => $developmentPackage->version,
            ],
        ],
        'platform' => [],
    ], JSON_THROW_ON_ERROR));

    expect($composerLockSummary->lockedPackages)
        ->toEqual(new LockedPackageCollection([$productionPackage, $developmentPackage]));
});
