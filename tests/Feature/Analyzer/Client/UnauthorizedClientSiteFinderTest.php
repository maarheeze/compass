<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Client\UnauthorizedClientSiteFinder;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Models\Site;

test('find sites finds a site whose client refuses the key', function (): void {
    $site = Site::factory()->create(['client_status' => ClientStatus::UNAUTHORIZED]);

    $unauthorizedClientSiteFinder = $this->app->make(UnauthorizedClientSiteFinder::class);
    $sitesWithUnauthorizedClient = $unauthorizedClientSiteFinder->findSites();

    expect($sitesWithUnauthorizedClient->sole()->is($site))
        ->toBeTrue();
});

test('find sites leaves out a site whose client accepts the key', function (): void {
    Site::factory()->create(['client_status' => ClientStatus::INSTALLED]);

    $unauthorizedClientSiteFinder = $this->app->make(UnauthorizedClientSiteFinder::class);
    $sitesWithUnauthorizedClient = $unauthorizedClientSiteFinder->findSites();

    expect($sitesWithUnauthorizedClient)
        ->toBeEmpty();
});
