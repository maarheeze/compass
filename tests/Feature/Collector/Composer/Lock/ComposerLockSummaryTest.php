<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Collector\Composer\Lock\ComposerLockSummary;

test('composer lock summary holds the php constraint it is given', function (): void {
    $phpConstraint = fake()->numerify('^#.#');

    $composerLockSummary = new ComposerLockSummary($phpConstraint, new ComposerLockPackageCollection());

    expect($composerLockSummary->phpConstraint)
        ->toBe($phpConstraint);
});

test('composer lock summary holds the locked packages it is given', function (): void {
    $lockedPackages = new ComposerLockPackageCollection();

    $composerLockSummary = new ComposerLockSummary(fake()->numerify('^#.#'), $lockedPackages);

    expect($composerLockSummary->lockedPackages)
        ->toBe($lockedPackages);
});
