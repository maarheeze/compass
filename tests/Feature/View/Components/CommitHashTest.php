<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\GitHubCommitUrlFactory;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\CommitHash;

test('url is the github page of the commit', function (): void {
    $repository = Repository::factory()->create();
    $commitHash = fake()->sha1();
    $gitHubCommitUrlFactory = $this->app->make(GitHubCommitUrlFactory::class);

    $commitHashComponent = $this->app->make(CommitHash::class, ['repository' => $repository, 'commitHash' => $commitHash]);

    expect($commitHashComponent->url)
        ->toBe($gitHubCommitUrlFactory->create($repository, $commitHash));
});

test('url is null when the repository is unknown', function (): void {
    $commitHashComponent = $this->app->make(CommitHash::class, ['repository' => null, 'commitHash' => fake()->sha1()]);

    expect($commitHashComponent->url)
        ->toBeNull();
});

test('url is null when there is no commit', function (): void {
    $commitHashComponent = $this->app->make(CommitHash::class, ['repository' => Repository::factory()->create(), 'commitHash' => null]);

    expect($commitHashComponent->url)
        ->toBeNull();
});
