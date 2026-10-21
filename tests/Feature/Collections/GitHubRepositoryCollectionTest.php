<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Tests\Fixtures\GitHubRepositoryFactory;

test('github repository collection holds the github repositories it is given', function (): void {
    $gitHubRepository = GitHubRepositoryFactory::make();

    $gitHubRepositoryCollection = new GitHubRepositoryCollection([$gitHubRepository]);

    expect($gitHubRepositoryCollection->all())
        ->toBe([$gitHubRepository]);
});
