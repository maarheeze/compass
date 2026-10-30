<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Tests\Fixtures\GitHubRepositoryFactory;

test('pages are queried at the github graphql api', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQuery->pages()->current();

    $http->assertSent(static function (HttpRequest $request): bool {
        if ($request->method() !== 'POST') {
            return false;
        }

        return $request->url() === 'https://api.github.com/graphql';
    });
});

test('pages query the first page for the organization without a cursor', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.github.organization', $organization);

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQuery->pages()->current();

    $http->assertSent(static function (HttpRequest $request) use ($organization): bool {
        return $request['variables'] === [
            'organization' => $organization,
            'cursor' => null,
        ];
    });
});

test('pages query the next page from the end cursor of the page before it', function (): void {
    $endCursor = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([
        'data' => [
            'organization' => [
                'repositories' => [
                    'pageInfo' => [
                        'hasNextPage' => true,
                        'endCursor' => $endCursor,
                    ],
                    'nodes' => [],
                ],
            ],
        ],
    ]);
    $responseSequence->push([
        'data' => [
            'organization' => [
                'repositories' => [
                    'pageInfo' => [
                        'hasNextPage' => false,
                        'endCursor' => null,
                    ],
                    'nodes' => [],
                ],
            ],
        ],
    ]);
    $http->fake(['*' => $responseSequence]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    iterator_to_array($repositoriesQuery->pages());

    $http->assertSent(static function (HttpRequest $request) use ($endCursor): bool {
        return $request['variables']['cursor'] === $endCursor;
    });
});

test('pages hold the github repositories in the response', function (): void {
    $gitHubRepository = GitHubRepositoryFactory::make();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [
                            [
                                'id' => $gitHubRepository->gitHubId,
                                'name' => $gitHubRepository->name,
                                'url' => $gitHubRepository->url,
                                'visibility' => $gitHubRepository->visibility->value,
                                'defaultBranchRef' => [
                                    'name' => $gitHubRepository->defaultBranch,
                                    'target' => [
                                        'oid' => $gitHubRepository->lastCommitSha,
                                        'committedDate' => $gitHubRepository->lastCommitCommittedAt->toIso8601String(),
                                    ],
                                ],
                                'composerJson' => [
                                    'oid' => $gitHubRepository->composerJsonOid,
                                ],
                                'composerLock' => [
                                    'oid' => $gitHubRepository->composerLockOid,
                                ],
                                'packageJson' => [
                                    'oid' => $gitHubRepository->packageJsonOid,
                                ],
                                'dependabotYml' => [
                                    'oid' => $gitHubRepository->dependabotYmlOid,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQueryResult = $repositoriesQuery->pages()->current();

    expect($repositoriesQueryResult->gitHubRepositories)
        ->toEqual(new GitHubRepositoryCollection([$gitHubRepository]));
});

test('pages skip repositories without a default branch', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [
                            [
                                'id' => fake()->sha1(),
                                'name' => fake()->slug(),
                                'visibility' => fake()->randomElement(RepositoryVisibility::cases())->value,
                                'defaultBranchRef' => null,
                                'composerJson' => null,
                                'composerLock' => null,
                                'packageJson' => null,
                                'dependabotYml' => null,
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQueryResult = $repositoriesQuery->pages()->current();

    expect($repositoriesQueryResult->gitHubRepositories)
        ->toBeEmpty();
});

test('pages end with the page without a next page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => fake()->sha1(),
                        ],
                        'nodes' => [],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    iterator_to_array($repositoriesQuery->pages());

    $http->assertSentCount(1);
});

test('pages throw when github answers with errors', function (): void {
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => null,
            ],
            'errors' => [
                [
                    'message' => fake()->sentence(),
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQuery->pages()->current();
})->throws(GitHubQueryFailed::class);

test('pages throw when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQuery->pages()->current();
})->throws(GitHubQueryFailed::class);
