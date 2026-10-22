<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\Repository;

test('handle queries the page of its cursor', function (): void {
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

    $collectRepositories = new CollectRepositories(CollectionRunId::generate(), $cursor);
    $this->app->call([$collectRepositories, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($cursor): bool {
        return $request['variables']['cursor'] === $cursor;
    });
});

test('handle stores the repositories of the page', function (): void {
    $repositoryResponse = [
        'id' => fake()->sha1(),
        'name' => fake()->slug(),
        'url' => fake()->url(),
        'visibility' => fake()->randomElement(RepositoryVisibility::cases())->value,
        'defaultBranchRef' => [
            'name' => fake()->word(),
            'target' => [
                'oid' => fake()->sha1(),
            ],
        ],
        'composerJson' => null,
        'composerLock' => null,
        'issues' => [
            'nodes' => [],
        ],
    ];
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'organization' => [
                    'repositories' => [
                        'pageInfo' => [
                            'hasNextPage' => false,
                            'endCursor' => null,
                        ],
                        'nodes' => [$repositoryResponse],
                    ],
                ],
            ],
        ]),
    ]);

    $collectRepositories = new CollectRepositories($collectionRunId, null);
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertDatabaseHas(Repository::class, [
        'name' => $repositoryResponse['name'],
        'collection_run_id' => $collectionRunId,
    ]);
});

test('handle chains the next page when there is one', function (): void {
    $endCursor = fake()->sha1();
    $collectionRunId = CollectionRunId::generate();
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

    $collectRepositories = new CollectRepositories($collectionRunId, null);
    $this->app->call([$collectRepositories, 'handle']);

    $collectRepositories->assertHasChain([new CollectRepositories($collectionRunId, $endCursor)]);
});

test('handle deletes the repositories of another run on the last page', function (): void {
    $repository = Repository::factory()->create();
    $this->app->make(HttpFactory::class)->fake([
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

    $collectRepositories = new CollectRepositories(CollectionRunId::generate(), null);
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertModelMissing($repository);
});

test('handle deletes the issues of another run on the last page', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $issue = Issue::factory()->create([
        'repository_id' => Repository::factory()->create(['collection_run_id' => $collectionRunId])->id,
    ]);
    $this->app->make(HttpFactory::class)->fake([
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

    $collectRepositories = new CollectRepositories($collectionRunId, null);
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertModelMissing($issue);
});
