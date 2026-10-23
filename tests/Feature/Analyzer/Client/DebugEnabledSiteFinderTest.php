<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Client\DebugEnabledSiteFinder;
use Maarheeze\Compass\Models\Site;

test('find sites finds a site with debug mode on', function (): void {
    $site = Site::factory()->create(['app_debug' => true]);

    $debugEnabledSiteFinder = $this->app->make(DebugEnabledSiteFinder::class);
    $sitesWithDebugEnabled = $debugEnabledSiteFinder->findSites();

    expect($sitesWithDebugEnabled->sole()->is($site))
        ->toBeTrue();
});

test('find sites leaves out a site with debug mode off', function (): void {
    Site::factory()->create(['app_debug' => false]);

    $debugEnabledSiteFinder = $this->app->make(DebugEnabledSiteFinder::class);
    $sitesWithDebugEnabled = $debugEnabledSiteFinder->findSites();

    expect($sitesWithDebugEnabled)
        ->toBeEmpty();
});

test('find sites leaves out a site without a report', function (): void {
    Site::factory()->create(['app_debug' => null]);

    $debugEnabledSiteFinder = $this->app->make(DebugEnabledSiteFinder::class);
    $sitesWithDebugEnabled = $debugEnabledSiteFinder->findSites();

    expect($sitesWithDebugEnabled)
        ->toBeEmpty();
});

test('find sites leaves out a missing site', function (): void {
    Site::factory()->create([
        'app_debug' => true,
        'missing_at' => fake()->dateTime(),
    ]);

    $debugEnabledSiteFinder = $this->app->make(DebugEnabledSiteFinder::class);
    $sitesWithDebugEnabled = $debugEnabledSiteFinder->findSites();

    expect($sitesWithDebugEnabled)
        ->toBeEmpty();
});
