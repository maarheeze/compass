<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\Events\PackageJsonChanged;
use Maarheeze\Compass\Collector\Events\RepositoriesCollected;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerLocks;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedDependabotYmls;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('handle stores the repositories of the page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repositoryResponse = [
        'id' => fake()->sha1(),
        'name' => fake()->slug(),
        'url' => fake()->url(),
        'visibility' => fake()->randomElement(['INTERNAL', 'PRIVATE', 'PUBLIC']),
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
    ];
    $runId = Uuid::generate();
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

    [$collectRepositories] = new CollectRepositories($runId, $source)->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    $this->assertDatabaseHas(Repository::class, [
        'source' => $source,
        'name' => $repositoryResponse['name'],
        'run_id' => $runId,
    ]);
});

test('handle marks the repositories of another run missing on the last page', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['source' => $source]);
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

    [$collectRepositories] = new CollectRepositories(Uuid::generate(), $source)->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    expect($repository->refresh()->missing_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('handle adds the collection of the composer files and dependabot yml to its batch', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
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

    [$collectRepositories, $batchFake] = new CollectRepositories(Uuid::generate(), $source)->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    expect(array_map(get_class(...), $batchFake->added))
        ->toBe([
            CollectChangedComposerJsons::class,
            CollectChangedComposerLocks::class,
            CollectChangedDependabotYmls::class,
        ]);
});

test('handle refuses a source without a repositories collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);

    $this->expectException(InvalidArgumentException::class);

    [$collectRepositories] = new CollectRepositories(Uuid::generate(), $key)->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);
});

test('handle raises repositories collected', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
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

    [$collectRepositories] = new CollectRepositories(Uuid::generate(), $source)->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    $eventFake->assertDispatched(
        RepositoriesCollected::class,
        static function (RepositoriesCollected $repositoriesCollected) use ($source): bool {
            return $repositoriesCollected->source === $source;
        },
    );
});

test('handle raises package json changed for a repository whose package json version changed', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $repositoryResponse = [
        'id' => fake()->sha1(),
        'name' => fake()->slug(),
        'url' => fake()->url(),
        'visibility' => fake()->randomElement(['INTERNAL', 'PRIVATE', 'PUBLIC']),
        'defaultBranchRef' => [
            'name' => fake()->word(),
            'target' => [
                'oid' => fake()->sha1(),
                'committedDate' => fake()->iso8601(),
            ],
        ],
        'composerJson' => null,
        'composerLock' => null,
        'packageJson' => [
            'oid' => fake()->sha1(),
        ],
        'dependabotYml' => null,
    ];
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

    [$collectRepositories] = new CollectRepositories(Uuid::generate(), $source)->withFakeBatch();
    $this->app->call([$collectRepositories, 'handle']);

    $eventFake->assertDispatched(PackageJsonChanged::class, static function (PackageJsonChanged $packageJsonChanged) use ($repositoryResponse): bool {
        return $packageJsonChanged->repository->github_id === $repositoryResponse['id'];
    });
});
