<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\RequiredPackageCollection;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonSummary;

test('composer json summary holds the type it is given', function (): void {
    $type = fake()->word();

    $composerJsonSummary = new ComposerJsonSummary(
        $type,
        fake()->numerify('^#.#'),
        fake()->optional()->word(),
        fake()->boolean(),
        new RequiredPackageCollection(),
    );

    expect($composerJsonSummary->type)
        ->toBe($type);
});

test('composer json summary holds the php constraint it is given', function (): void {
    $phpConstraint = fake()->numerify('^#.#');

    $composerJsonSummary = new ComposerJsonSummary(
        fake()->word(),
        $phpConstraint,
        fake()->optional()->word(),
        fake()->boolean(),
        new RequiredPackageCollection(),
    );

    expect($composerJsonSummary->phpConstraint)
        ->toBe($phpConstraint);
});

test('composer json summary holds the minimum stability it is given', function (): void {
    $minimumStability = fake()->word();

    $composerJsonSummary = new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        $minimumStability,
        fake()->boolean(),
        new RequiredPackageCollection(),
    );

    expect($composerJsonSummary->minimumStability)
        ->toBe($minimumStability);
});

test('composer json summary holds the prefer stable it is given', function (): void {
    $preferStable = fake()->boolean();

    $composerJsonSummary = new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        fake()->optional()->word(),
        $preferStable,
        new RequiredPackageCollection(),
    );

    expect($composerJsonSummary->preferStable)
        ->toBe($preferStable);
});

test('composer json summary holds the required packages it is given', function (): void {
    $requiredPackages = new RequiredPackageCollection();

    $composerJsonSummary = new ComposerJsonSummary(
        fake()->word(),
        fake()->numerify('^#.#'),
        fake()->optional()->word(),
        fake()->boolean(),
        $requiredPackages,
    );

    expect($composerJsonSummary->requiredPackages)
        ->toBe($requiredPackages);
});
