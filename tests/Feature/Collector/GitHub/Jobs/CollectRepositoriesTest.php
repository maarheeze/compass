<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
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
        'name' => fake()->slug(),
        'visibility' => fake()->randomElement(RepositoryVisibility::cases())->value,
        'defaultBranchRef' => [
            'name' => fake()->word(),
            'target' => [
                'oid' => fake()->sha1(),
            ],
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

test('handle dispatches the next page when there is one', function (): void {
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

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectRepositories = new CollectRepositories($collectionRunId, null);
    $this->app->call([$collectRepositories, 'handle']);

    $expectedCollectRepositories = new CollectRepositories($collectionRunId, $endCursor);
    expect($busFake->dispatched(CollectRepositories::class)->all())
        ->toEqual([$expectedCollectRepositories]);
});

test('handle marks repositories of another run inactive on the last page', function (): void {
    $repository = Repository::factory()->create(['is_active' => true]);
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

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'is_active' => false,
    ]);
});
