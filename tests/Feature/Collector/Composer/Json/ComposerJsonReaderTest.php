<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\RequiredPackageCollection;
use Maarheeze\Compass\Collector\Composer\Json\ComposerJsonReader;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Collector\Composer\Json\RequiredPackage;

test('type is the type in the composer json', function (): void {
    $type = fake()->word();

    $composerJsonReader = $this->app->make(ComposerJsonReader::class);
    $composerJsonSummary = $composerJsonReader->read(json_encode(['type' => $type], JSON_THROW_ON_ERROR));

    expect($composerJsonSummary->type)
        ->toBe($type);
});

test('type is null when the composer json has none', function (): void {
    $composerJsonReader = $this->app->make(ComposerJsonReader::class);
    $composerJsonSummary = $composerJsonReader->read(json_encode([
        'name' => fake()->slug(),
    ], JSON_THROW_ON_ERROR));

    expect($composerJsonSummary->type)
        ->toBeNull();
});

test('php constraint is the required php version', function (): void {
    $phpConstraint = fake()->numerify('^#.#');

    $composerJsonReader = $this->app->make(ComposerJsonReader::class);
    $composerJsonSummary = $composerJsonReader->read(json_encode([
        'require' => [
            'php' => $phpConstraint,
        ],
    ], JSON_THROW_ON_ERROR));

    expect($composerJsonSummary->phpConstraint)
        ->toBe($phpConstraint);
});

test('php constraint is null when php is not required', function (): void {
    $composerJsonReader = $this->app->make(ComposerJsonReader::class);
    $composerJsonSummary = $composerJsonReader->read(json_encode([
        'name' => fake()->slug(),
    ], JSON_THROW_ON_ERROR));

    expect($composerJsonSummary->phpConstraint)
        ->toBeNull();
});

test('required packages are the packages of require and require dev with their section', function (): void {
    $productionPackage = new RequiredPackage(
        sprintf('%s/%s', fake()->slug(), fake()->slug()),
        fake()->numerify('^#.#'),
        PackageSection::PRODUCTION,
    );
    $developmentPackage = new RequiredPackage(
        sprintf('%s/%s', fake()->slug(), fake()->slug()),
        fake()->numerify('^#.#'),
        PackageSection::DEVELOPMENT,
    );

    $composerJsonReader = $this->app->make(ComposerJsonReader::class);
    $composerJsonSummary = $composerJsonReader->read(json_encode([
        'require' => [
            $productionPackage->name => $productionPackage->constraint,
        ],
        'require-dev' => [
            $developmentPackage->name => $developmentPackage->constraint,
        ],
    ], JSON_THROW_ON_ERROR));

    expect($composerJsonSummary->requiredPackages)
        ->toEqual(new RequiredPackageCollection([$productionPackage, $developmentPackage]));
});

test('required packages leave out platform requirements', function (): void {
    $composerJsonReader = $this->app->make(ComposerJsonReader::class);
    $composerJsonSummary = $composerJsonReader->read(json_encode([
        'require' => [
            'php' => fake()->numerify('^#.#'),
            sprintf('ext-%s', fake()->word()) => '*',
        ],
    ], JSON_THROW_ON_ERROR));

    expect($composerJsonSummary->requiredPackages)
        ->toBeEmpty();
});
