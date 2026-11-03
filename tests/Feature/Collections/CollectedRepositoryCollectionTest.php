<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Tests\Fixtures\CollectedRepositoryFactory;

test('collected repository collection holds the collected repositories it is given', function (): void {
    $collectedRepository = CollectedRepositoryFactory::make();

    $collectedRepositoryCollection = new CollectedRepositoryCollection([$collectedRepository]);

    expect($collectedRepositoryCollection->all())
        ->toBe([$collectedRepository]);
});

test('external ids are the external ids of the repositories', function (): void {
    $collectedRepository = CollectedRepositoryFactory::make();

    $collectedRepositoryCollection = new CollectedRepositoryCollection([$collectedRepository]);
    $externalIds = $collectedRepositoryCollection->externalIds();

    expect($externalIds->all())
        ->toBe([$collectedRepository->externalId]);
});
