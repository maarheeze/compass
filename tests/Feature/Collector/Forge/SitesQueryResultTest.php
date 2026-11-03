<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\Forge\SitesQueryResult;
use Maarheeze\Compass\Tests\Fixtures\CollectedSiteFactory;

test('sites query result holds the collected sites it is given', function (): void {
    $collectedSites = new CollectedSiteCollection([CollectedSiteFactory::make()]);

    $sitesQueryResult = new SitesQueryResult($collectedSites, fake()->sha1());

    expect($sitesQueryResult->collectedSites)
        ->toBe($collectedSites);
});

test('sites query result holds the next cursor it is given', function (): void {
    $nextCursor = fake()->sha1();

    $sitesQueryResult = new SitesQueryResult(new CollectedSiteCollection(), $nextCursor);

    expect($sitesQueryResult->nextCursor)
        ->toBe($nextCursor);
});
