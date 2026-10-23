<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Client\NonProductionSiteFinder;
use Maarheeze\Compass\Models\Site;

test('find sites finds a site outside production', function (): void {
    $site = Site::factory()->create(['app_env' => 'staging']);

    $nonProductionSiteFinder = $this->app->make(NonProductionSiteFinder::class);
    $sitesOutsideProduction = $nonProductionSiteFinder->findSites();

    expect($sitesOutsideProduction->sole()->is($site))
        ->toBeTrue();
});

test('find sites leaves out a site in production', function (): void {
    Site::factory()->create(['app_env' => 'production']);

    $nonProductionSiteFinder = $this->app->make(NonProductionSiteFinder::class);
    $sitesOutsideProduction = $nonProductionSiteFinder->findSites();

    expect($sitesOutsideProduction)
        ->toBeEmpty();
});

test('find sites leaves out a site without a report', function (): void {
    Site::factory()->create(['app_env' => null]);

    $nonProductionSiteFinder = $this->app->make(NonProductionSiteFinder::class);
    $sitesOutsideProduction = $nonProductionSiteFinder->findSites();

    expect($sitesOutsideProduction)
        ->toBeEmpty();
});

test('find sites leaves out a missing site', function (): void {
    Site::factory()->create([
        'app_env' => 'staging',
        'missing_at' => fake()->dateTime(),
    ]);

    $nonProductionSiteFinder = $this->app->make(NonProductionSiteFinder::class);
    $sitesOutsideProduction = $nonProductionSiteFinder->findSites();

    expect($sitesOutsideProduction)
        ->toBeEmpty();
});
