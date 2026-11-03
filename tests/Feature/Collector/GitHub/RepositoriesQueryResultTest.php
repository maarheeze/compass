<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQueryResult;
use Maarheeze\Compass\Tests\Fixtures\CollectedRepositoryFactory;

test('repositories query result holds the collected repositories it is given', function (): void {
    $collectedRepositories = new CollectedRepositoryCollection([CollectedRepositoryFactory::make()]);

    $repositoriesQueryResult = new RepositoriesQueryResult($collectedRepositories, fake()->sha1());

    expect($repositoriesQueryResult->collectedRepositories)
        ->toBe($collectedRepositories);
});

test('repositories query result holds the next cursor it is given', function (): void {
    $nextCursor = fake()->sha1();

    $repositoriesQueryResult = new RepositoriesQueryResult(new CollectedRepositoryCollection(), $nextCursor);

    expect($repositoriesQueryResult->nextCursor)
        ->toBe($nextCursor);
});
