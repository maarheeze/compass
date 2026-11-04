<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

test('handle stores the sites of the page', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'forge',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $forgeId = fake()->numerify('#######');
    $serverId = fake()->numerify('####');
    $runId = Uuid::generate();
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
                        'quick_deploy' => null,
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

    [$collectSites] = new CollectSites($runId, $sourceKey)->withFakeBatch();
    $this->app->call([$collectSites, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $forgeId,
        'run_id' => $runId,
    ]);
});

test('handle marks the sites of another run missing on the last page', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'forge',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $site = Site::factory()->create();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    [$collectSites] = new CollectSites(Uuid::generate(), $sourceKey)->withFakeBatch();
    $this->app->call([$collectSites, 'handle']);

    expect($site->refresh()->missing_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('handle refuses a source without a sites collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);

    $this->expectException(InvalidArgumentException::class);

    [$collectSites] = new CollectSites(Uuid::generate(), $key)->withFakeBatch();
    $this->app->call([$collectSites, 'handle']);
});

test('handle raises sites collected', function (): void {
    $sourceKey = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $sourceKey => [
            'driver' => 'forge',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    [$collectSites] = new CollectSites(Uuid::generate(), $sourceKey)->withFakeBatch();
    $this->app->call([$collectSites, 'handle']);

    $eventFake->assertDispatched(SitesCollected::class);
});
