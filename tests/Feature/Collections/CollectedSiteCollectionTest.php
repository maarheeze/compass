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
