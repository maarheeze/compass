<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerJsonPackage;

test('named keeps a package with the name', function (): void {
    $requiredPackage = ComposerJsonPackage::factory()->create();

    $requiredPackagesNamed = ComposerJsonPackage::query()
        ->named($requiredPackage->name)
        ->get();

    expect($requiredPackagesNamed->sole()->is($requiredPackage))
        ->toBeTrue();
});

test('named leaves out a package with another name', function (): void {
    ComposerJsonPackage::factory()->create(['name' => fake()->unique()->slug()]);

    $requiredPackagesNamed = ComposerJsonPackage::query()
        ->named(fake()->unique()->slug())
        ->get();

    expect($requiredPackagesNamed)
        ->toBeEmpty();
});

test('development keeps a development package', function (): void {
    $developmentPackage = ComposerJsonPackage::factory()->create(['section' => PackageSection::DEVELOPMENT]);

    $developmentPackages = ComposerJsonPackage::query()
        ->development()
        ->get();

    expect($developmentPackages->sole()->is($developmentPackage))
        ->toBeTrue();
});

test('development leaves out a production package', function (): void {
    ComposerJsonPackage::factory()->create(['section' => PackageSection::PRODUCTION]);

    $developmentPackages = ComposerJsonPackage::query()
        ->development()
        ->get();

    expect($developmentPackages)
        ->toBeEmpty();
});

test('listed is the packages ordered by name', function (): void {
    $laterRequiredPackage = ComposerJsonPackage::factory()->create(['name' => sprintf('b%s', fake()->slug())]);
    $earlierRequiredPackage = ComposerJsonPackage::factory()->create(['name' => sprintf('a%s', fake()->slug())]);

    $listedRequiredPackages = ComposerJsonPackage::query()->listed();

    expect($listedRequiredPackages->modelKeys())
        ->toEqual([$earlierRequiredPackage->getKey(), $laterRequiredPackage->getKey()]);
});

test('of repository keeps only the packages of the repository', function (): void {
    $requiredPackage = ComposerJsonPackage::factory()->create();
    ComposerJsonPackage::factory()->create();

    $requiredPackagesOfRepository = ComposerJsonPackage::query()
        ->ofRepository($requiredPackage->composerJson->repository)
        ->get();

    expect($requiredPackagesOfRepository->sole()->is($requiredPackage))
        ->toBeTrue();
});

test('production keeps a production package', function (): void {
    $productionPackage = ComposerJsonPackage::factory()->create(['section' => PackageSection::PRODUCTION]);

    $productionPackages = ComposerJsonPackage::query()
        ->production()
        ->get();

    expect($productionPackages->sole()->is($productionPackage))
        ->toBeTrue();
});

test('production leaves out a development package', function (): void {
    ComposerJsonPackage::factory()->create(['section' => PackageSection::DEVELOPMENT]);

    $productionPackages = ComposerJsonPackage::query()
        ->production()
        ->get();

    expect($productionPackages)
        ->toBeEmpty();
});
