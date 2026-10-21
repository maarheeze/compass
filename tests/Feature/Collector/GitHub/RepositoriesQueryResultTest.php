<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQueryResult;
use Maarheeze\Compass\Tests\Fixtures\GitHubRepositoryFactory;

test('repositories query result holds the github repositories it is given', function (): void {
    $gitHubRepository = GitHubRepositoryFactory::make();
    $gitHubRepositories = new GitHubRepositoryCollection([$gitHubRepository]);

    $repositoriesQueryResult = new RepositoriesQueryResult($gitHubRepositories, fake()->sha1());

    expect($repositoriesQueryResult->gitHubRepositories)
        ->toBe($gitHubRepositories);
});

test('repositories query result holds the next cursor it is given', function (): void {
    $nextCursor = fake()->sha1();

    $repositoriesQueryResult = new RepositoriesQueryResult(new GitHubRepositoryCollection(), $nextCursor);

    expect($repositoriesQueryResult->nextCursor)
        ->toBe($nextCursor);
});
