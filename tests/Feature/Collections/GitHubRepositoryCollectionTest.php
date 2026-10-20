<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\GitHubRepository;

test('github repository collection holds the github repositories it is given', function (): void {
    $gitHubRepository = new GitHubRepository(fake()->slug(), fake()->word());

    $gitHubRepositoryCollection = new GitHubRepositoryCollection([$gitHubRepository]);

    expect($gitHubRepositoryCollection->all())
        ->toBe([$gitHubRepository]);
});
