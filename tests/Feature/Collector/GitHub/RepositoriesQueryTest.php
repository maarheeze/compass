<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;
use Maarheeze\Compass\Collector\GitHub\RepositoriesQuery;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Tests\Fixtures\GitHubRepositoryFactory;

test('page is queried at the github graphql api', function (): void {
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
    $repositoriesQuery->page(fake()->sha1());

    $http->assertSent(static function (HttpRequest $request): bool {
        if ($request->method() !== 'POST') {
            return false;
        }

        return $request->url() === 'https://api.github.com/graphql';
    });
});

test('page is queried for the organization from the cursor', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.github_organization', $organization);
    $cursor = fake()->sha1();

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
    $repositoriesQuery->page($cursor);

    $http->assertSent(static function (HttpRequest $request) use ($organization, $cursor): bool {
        return $request['variables'] === [
            'organization' => $organization,
            'cursor' => $cursor,
        ];
    });
});

test('page holds the github repositories in the response', function (): void {
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
                                'visibility' => $gitHubRepository->visibility->value,
                                'defaultBranchRef' => [
                                    'name' => $gitHubRepository->defaultBranch,
                                    'target' => [
                                        'oid' => $gitHubRepository->lastCommitSha,
                                    ],
                                ],
                                'composerJson' => [
                                    'oid' => $gitHubRepository->composerJsonOid,
                                ],
                                'composerLock' => [
                                    'oid' => $gitHubRepository->composerLockOid,
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQueryResult = $repositoriesQuery->page(null);

    expect($repositoriesQueryResult->gitHubRepositories)
        ->toEqual(new GitHubRepositoryCollection([$gitHubRepository]));
});

test('page skips repositories without a default branch', function (): void {
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
                            ],
                        ],
                    ],
                ],
            ],
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQueryResult = $repositoriesQuery->page(null);

    expect($repositoriesQueryResult->gitHubRepositories)
        ->toBeEmpty();
});

test('page holds the end cursor as next cursor when there is a next page', function (): void {
    $endCursor = fake()->sha1();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
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
        ]),
    ]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQueryResult = $repositoriesQuery->page(null);

    expect($repositoriesQueryResult->nextCursor)
        ->toBe($endCursor);
});

test('page holds no next cursor on the last page', function (): void {
    $this->app->make(HttpFactory::class)->fake([
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
    $repositoriesQueryResult = $repositoriesQuery->page(null);

    expect($repositoriesQueryResult->nextCursor)
        ->toBeNull();
});

test('page throws when github answers with errors', function (): void {
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
    $repositoriesQuery->page(null);
})->throws(GitHubQueryFailed::class);

test('page throws when github refuses the request', function (): void {
    $this->app->make(HttpFactory::class)->fake(['*' => HttpFactory::response(null, 401)]);

    $repositoriesQuery = $this->app->make(RepositoriesQuery::class);
    $repositoriesQuery->page(null);
})->throws(GitHubQueryFailed::class);
