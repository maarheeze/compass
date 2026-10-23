<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\MissingSiteFinder;
use Maarheeze\Compass\Models\Site;

test('find sites finds a missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingSiteFinder = $this->app->make(MissingSiteFinder::class);
    $missingSites = $missingSiteFinder->findSites();

    expect($missingSites->sole()->is($site))
        ->toBeTrue();
});

test('find sites leaves out a collected site', function (): void {
    Site::factory()->create();

    $missingSiteFinder = $this->app->make(MissingSiteFinder::class);
    $missingSites = $missingSiteFinder->findSites();

    expect($missingSites)
        ->toBeEmpty();
});

test('find sites leaves out a missing site that is ignored', function (): void {
    Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingSiteFinder = $this->app->make(MissingSiteFinder::class);
    $missingSites = $missingSiteFinder->findSites();

    expect($missingSites)
        ->toBeEmpty();
});
