<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\LockedPackageCollection;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSummary;

test('composer lock summary holds the php constraint it is given', function (): void {
    $phpConstraint = fake()->numerify('^#.#');

    $composerLockSummary = new ComposerLockSummary($phpConstraint, new LockedPackageCollection());

    expect($composerLockSummary->phpConstraint)
        ->toBe($phpConstraint);
});

test('composer lock summary holds the locked packages it is given', function (): void {
    $lockedPackages = new LockedPackageCollection();

    $composerLockSummary = new ComposerLockSummary(fake()->numerify('^#.#'), $lockedPackages);

    expect($composerLockSummary->lockedPackages)
        ->toBe($lockedPackages);
});
