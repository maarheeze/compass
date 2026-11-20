<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Collector\SiteDomains\CollectedSiteDomain;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainSynchroniser;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteDomain;

test('store site domains creates a domain for a new collected domain', function (): void {
    $site = Site::factory()->create();
    $collectedSiteDomain = new CollectedSiteDomain(
        fake()->domainName(),
        fake()->randomElement(SiteDomainType::cases()),
        fake()->randomElement(SiteDomainStatus::cases()),
    );

    $siteDomainSynchroniser = $this->app->make(SiteDomainSynchroniser::class);
    $siteDomainSynchroniser->storeSiteDomains($site, new CollectedSiteDomainCollection([$collectedSiteDomain]));

    $this->assertDatabaseHas(SiteDomain::class, [
        'site_id' => $site->id,
        'name' => $collectedSiteDomain->name,
        'type' => $collectedSiteDomain->type,
        'status' => $collectedSiteDomain->status,
    ]);
});

test('store site domains updates the status of a known domain', function (): void {
    $siteDomain = SiteDomain::factory()->create(['status' => SiteDomainStatus::ENABLED]);
    $collectedSiteDomain = new CollectedSiteDomain($siteDomain->name, $siteDomain->type, SiteDomainStatus::DISABLED);

    $siteDomainSynchroniser = $this->app->make(SiteDomainSynchroniser::class);
    $siteDomainSynchroniser->storeSiteDomains($siteDomain->site, new CollectedSiteDomainCollection([$collectedSiteDomain]));

    $this->assertDatabaseHas(SiteDomain::class, [
        'id' => $siteDomain->id,
        'status' => SiteDomainStatus::DISABLED,
    ]);
});

test('store site domains deletes a domain that is no longer collected', function (): void {
    $siteDomain = SiteDomain::factory()->create();

    $siteDomainSynchroniser = $this->app->make(SiteDomainSynchroniser::class);
    $siteDomainSynchroniser->storeSiteDomains($siteDomain->site, new CollectedSiteDomainCollection());

    $this->assertModelMissing($siteDomain);
});

test('store site domains keeps the domains of another site', function (): void {
    $siteDomain = SiteDomain::factory()->create();

    $siteDomainSynchroniser = $this->app->make(SiteDomainSynchroniser::class);
    $siteDomainSynchroniser->storeSiteDomains(Site::factory()->create(), new CollectedSiteDomainCollection());

    $this->assertModelExists($siteDomain);
});
