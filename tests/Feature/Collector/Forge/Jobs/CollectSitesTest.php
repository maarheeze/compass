<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Models\Site;

test('handle queries the page of its cursor', function (): void {
    $cursor = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $collectSites = new CollectSites(CollectionRunId::generate(), $cursor);
    $this->app->call([$collectSites, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($cursor): bool {
        return $request['page[cursor]'] === $cursor;
    });
});

test('handle stores the sites of the page', function (): void {
    $forgeId = fake()->numerify('#######');
    $serverId = fake()->numerify('####');
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => $forgeId,
                    'type' => 'sites',
                    'attributes' => [
                        'name' => fake()->domainName(),
                        'url' => fake()->url(),
                        'status' => 'deployed',
                        'php_version' => null,
                        'repository' => [
                            'url' => null,
                            'branch' => null,
                        ],
                    ],
                    'relationships' => [
                        'server' => [
                            'data' => [
                                'type' => 'servers',
                                'id' => $serverId,
                            ],
                        ],
                        'latestDeployment' => [
                            'data' => null,
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'id' => $serverId,
                    'type' => 'servers',
                    'attributes' => [
                        'slug' => fake()->slug(),
                        'name' => fake()->slug(),
                        'ip_address' => null,
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $collectSites = new CollectSites($collectionRunId, null);
    $this->app->call([$collectSites, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $forgeId,
        'collection_run_id' => $collectionRunId,
    ]);
});

test('handle chains the next page when there is one', function (): void {
    $nextCursor = fake()->sha1();
    $collectionRunId = CollectionRunId::generate();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => $nextCursor,
            ],
        ]),
    ]);

    $collectSites = new CollectSites($collectionRunId, null);
    $this->app->call([$collectSites, 'handle']);

    $collectSites->assertHasChain([new CollectSites($collectionRunId, $nextCursor)]);
});

test('handle marks sites of another run inactive on the last page', function (): void {
    $site = Site::factory()->create(['is_active' => true]);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $collectSites = new CollectSites(CollectionRunId::generate(), null);
    $this->app->call([$collectSites, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'is_active' => false,
    ]);
});
