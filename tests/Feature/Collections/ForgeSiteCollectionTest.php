<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\ForgeSiteCollection;
use Maarheeze\Compass\Tests\Fixtures\ForgeSiteFactory;

test('forge site collection holds the forge sites it is given', function (): void {
    $forgeSite = ForgeSiteFactory::make();

    $forgeSiteCollection = new ForgeSiteCollection([$forgeSite]);

    expect($forgeSiteCollection->all())
        ->toBe([$forgeSite]);
});
