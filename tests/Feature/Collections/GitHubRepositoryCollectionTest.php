<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\GitHub\GitHubRepository;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;

test('github repository collection holds the github repositories it is given', function (): void {
    $gitHubRepository = new GitHubRepository(
        fake()->slug(),
        fake()->word(),
        fake()->sha1(),
        fake()->randomElement(RepositoryVisibility::cases()),
    );

    $gitHubRepositoryCollection = new GitHubRepositoryCollection([$gitHubRepository]);

    expect($gitHubRepositoryCollection->all())
        ->toBe([$gitHubRepository]);
});
