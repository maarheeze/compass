<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Tests\Fixtures\CollectedSiteFactory;

test('collected site collection holds the collected sites it is given', function (): void {
    $collectedSite = CollectedSiteFactory::make();

    $collectedSiteCollection = new CollectedSiteCollection([$collectedSite]);

    expect($collectedSiteCollection->all())
        ->toBe([$collectedSite]);
});

test('on servers matching are the collected sites whose server name matches the server pattern', function (): void {
    $matchingCollectedSite = CollectedSiteFactory::make();
    $collectedSiteCollection = new CollectedSiteCollection([CollectedSiteFactory::make(), $matchingCollectedSite]);

    $collectedSitesOnServersMatching = $collectedSiteCollection->onServersMatching(
        sprintf('/\\A%s\\z/', preg_quote($matchingCollectedSite->serverName, '/')),
    );

    expect($collectedSitesOnServersMatching->all())
        ->toBe([$matchingCollectedSite]);
});
