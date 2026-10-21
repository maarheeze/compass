<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\ForgeSiteCollection;
use Maarheeze\Compass\Collector\Forge\SitesQueryResult;
use Maarheeze\Compass\Tests\Fixtures\ForgeSiteFactory;

test('sites query result holds the forge sites it is given', function (): void {
    $forgeSites = new ForgeSiteCollection([ForgeSiteFactory::make()]);

    $sitesQueryResult = new SitesQueryResult($forgeSites, fake()->sha1());

    expect($sitesQueryResult->forgeSites)
        ->toBe($forgeSites);
});

test('sites query result holds the next cursor it is given', function (): void {
    $nextCursor = fake()->sha1();

    $sitesQueryResult = new SitesQueryResult(new ForgeSiteCollection(), $nextCursor);

    expect($sitesQueryResult->nextCursor)
        ->toBe($nextCursor);
});
