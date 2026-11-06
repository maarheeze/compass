<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\Events\SitesCollected;
use Maarheeze\Compass\Collector\Forge\Jobs\CollectSites;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

test('handle stores the sites of the page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::FORGE,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
            'auto_deploy_grace_minutes' => fake()->numberBetween(1, 60),
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

    [$collectSites] = new CollectSites($runId, $source)->withFakeBatch();
    $this->app->call([$collectSites, 'handle']);

    $this->assertDatabaseHas(Site::class, [
        'source' => $source,
        'forge_id' => $forgeId,
        'run_id' => $runId,
    ]);
});

test('handle marks the sites of another run missing on the last page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::FORGE,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
            'auto_deploy_grace_minutes' => fake()->numberBetween(1, 60),
        ],
    ]);
    $site = Site::factory()->create(['source' => $source]);
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    [$collectSites] = new CollectSites(Uuid::generate(), $source)->withFakeBatch();
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

test('handle refuses a source without auto deploy grace minutes before collecting', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::FORGE,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $this->app->make(HttpFactory::class)->preventStrayRequests();

    $this->expectException(InvalidArgumentException::class);

    [$collectSites] = new CollectSites(Uuid::generate(), $source)->withFakeBatch();
    $this->app->call([$collectSites, 'handle']);
});

test('handle raises sites collected', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::FORGE,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
            'auto_deploy_grace_minutes' => fake()->numberBetween(1, 60),
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

    [$collectSites] = new CollectSites(Uuid::generate(), $source)->withFakeBatch();
    $this->app->call([$collectSites, 'handle']);

    $eventFake->assertDispatched(
        SitesCollected::class,
        static function (SitesCollected $sitesCollected) use ($source): bool {
            return $sitesCollected->source === $source;
        },
    );
});
