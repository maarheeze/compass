<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Tests\Fixtures\CollectedRepositoryFactory;

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

    $repositoriesQuery = new RepositoriesQuery(new GitHubClient($http, fake()->sha1()), fake()->slug());
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

    $repositoriesQuery = new RepositoriesQuery(new GitHubClient($http, fake()->sha1()), $organization);
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

    $repositoriesQuery = new RepositoriesQuery(new GitHubClient($http, fake()->sha1()), fake()->slug());
    iterator_to_array($repositoriesQuery->pages());

    $http->assertSent(static function (HttpRequest $request) use ($endCursor): bool {
        return $request['variables']['cursor'] === $endCursor;
    });
});

test('pages hold the collected repositories in the response', function (): void {
    $collectedRepository = CollectedRepositoryFactory::withVisibility(RepositoryVisibility::PUBLIC);
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
                                'id' => $collectedRepository->externalId,
                                'name' => $collectedRepository->name,
                                'nameWithOwner' => $collectedRepository->fullName,
                                'url' => $collectedRepository->url,
                                'visibility' => 'PUBLIC',
                                'defaultBranchRef' => [
                                    'name' => $collectedRepository->defaultBranch,
                                    'target' => [
                                        'oid' => $collectedRepository->lastCommitSha,
                                        'committedDate' => $collectedRepository->lastCommitCommittedAt->toIso8601String(),
                                    ],
                                ],
                                'composerJson' => [
                                    'oid' => $collectedRepository->composerJsonVersion,
                                ],
                                'composerLock' => [
                                    'oid' => $collectedRepository->composerLockVersion,
                                ],
                                'packageJson' => [
                                    'oid' => $collectedRepository->packageJsonVersion,
                                ],
                                'dependabotYml' => [
                                    'oid' => $collectedRepository->dependabotYmlVersion,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = new RepositoriesQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $repositoriesQueryResult = $repositoriesQuery->pages()->current();

    expect($repositoriesQueryResult->collectedRepositories)
        ->toEqual(new CollectedRepositoryCollection([$collectedRepository]));
});

test('pages map the visibility of github to the visibility of the collected repository', function (
    string $gitHubVisibility,
    RepositoryVisibility $visibility,
): void {
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
                        'nodes' => [
                            [
                                'id' => fake()->sha1(),
                                'name' => fake()->slug(),
                                'nameWithOwner' => sprintf('%s/%s', fake()->slug(), fake()->slug()),
                                'url' => fake()->url(),
                                'visibility' => $gitHubVisibility,
                                'defaultBranchRef' => [
                                    'name' => fake()->word(),
                                    'target' => [
                                        'oid' => fake()->sha1(),
                                        'committedDate' => fake()->iso8601(),
                                    ],
                                ],
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

    $repositoriesQuery = new RepositoriesQuery(new GitHubClient($http, fake()->sha1()), fake()->slug());
    $repositoriesQueryResult = $repositoriesQuery->pages()->current();

    expect($repositoriesQueryResult->collectedRepositories->sole()->visibility)
        ->toBe($visibility);
})->with([
    'internal' => ['INTERNAL', RepositoryVisibility::INTERNAL],
    'private' => ['PRIVATE', RepositoryVisibility::PRIVATE],
    'public' => ['PUBLIC', RepositoryVisibility::PUBLIC],
]);

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
                                'nameWithOwner' => sprintf('%s/%s', fake()->slug(), fake()->slug()),
                                'visibility' => fake()->randomElement(['INTERNAL', 'PRIVATE', 'PUBLIC']),
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

    $repositoriesQuery = new RepositoriesQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $repositoriesQueryResult = $repositoriesQuery->pages()->current();

    expect($repositoriesQueryResult->collectedRepositories)
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

    $repositoriesQuery = new RepositoriesQuery(new GitHubClient($http, fake()->sha1()), fake()->slug());
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

    $repositoriesQuery = new RepositoriesQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $repositoriesQuery->pages()->current();
})->throws(GitHubQueryFailed::class);

test('pages throw when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $repositoriesQuery = new RepositoriesQuery(
        new GitHubClient($this->app->make(HttpFactory::class), fake()->sha1()),
        fake()->slug(),
    );
    $repositoriesQuery->pages()->current();
})->throws(GitHubQueryFailed::class);
