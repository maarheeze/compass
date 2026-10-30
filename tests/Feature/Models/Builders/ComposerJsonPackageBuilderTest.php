<?php

declare(strict_types=1);

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
