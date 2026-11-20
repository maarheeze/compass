<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Collector\SiteDomains\CollectedSiteDomain;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;

test('names are the names of the site domains', function (): void {
    $collectedSiteDomain = new CollectedSiteDomain(
        fake()->domainName(),
        fake()->randomElement(SiteDomainType::cases()),
        fake()->randomElement(SiteDomainStatus::cases()),
    );

    $collectedSiteDomainCollection = new CollectedSiteDomainCollection([$collectedSiteDomain]);
    $names = $collectedSiteDomainCollection->names();

    expect($names->all())
        ->toBe([$collectedSiteDomain->name]);
});
