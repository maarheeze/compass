<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerLock;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\Repository;

test('handle queries the blob of the current composer lock oid', function (): void {
    $repository = Repository::factory()->create(['composer_lock_oid' => fake()->sha1()]);

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => json_encode([
                            'packages' => [],
                            'packages-dev' => [],
                            'platform' => [],
                        ], JSON_THROW_ON_ERROR),
                    ],
                ],
            ],
        ]),
    ]);

    $collectComposerLock = new CollectComposerLock($repository, 'github');
    $this->app->call([$collectComposerLock, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($repository): bool {
        if ($request['variables']['name'] !== $repository->name) {
            return false;
        }

        return $request['variables']['oid'] === $repository->composer_lock_oid;
    });
});

test('handle stores the composer lock with its oid', function (): void {
    $repository = Repository::factory()->create(['composer_lock_oid' => fake()->sha1()]);
    $phpConstraint = fake()->numerify('^#.#');
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => json_encode([
                            'packages' => [],
                            'packages-dev' => [],
                            'platform' => [
                                'php' => $phpConstraint,
                            ],
                        ], JSON_THROW_ON_ERROR),
                    ],
                ],
            ],
        ]),
    ]);

    $collectComposerLock = new CollectComposerLock($repository, 'github');
    $this->app->call([$collectComposerLock, 'handle']);

    $this->assertDatabaseHas(ComposerLock::class, [
        'repository_id' => $repository->id,
        'oid' => $repository->composer_lock_oid,
        'php_constraint' => $phpConstraint,
    ]);
});

test('handle removes the composer lock when the repository no longer has one', function (): void {
    $repository = Repository::factory()->create(['composer_lock_oid' => null]);
    $composerLock = ComposerLock::factory()->create(['repository_id' => $repository->id]);

    $collectComposerLock = new CollectComposerLock($repository, 'github');
    $this->app->call([$collectComposerLock, 'handle']);

    $this->assertDatabaseMissing(ComposerLock::class, ['id' => $composerLock->id]);
});

test('handle adds the repository to the log context', function (): void {
    $repository = Repository::factory()->create(['composer_lock_oid' => null]);

    $collectComposerLock = new CollectComposerLock($repository, 'github');
    $this->app->call([$collectComposerLock, 'handle']);

    expect($this->app->make(Context::class)->get('repository'))
        ->toBe($repository->name);
});

test('handle refuses a source without a composer files collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);
    $repository = Repository::factory()->create();

    $this->expectException(InvalidArgumentException::class);

    $collectComposerLock = new CollectComposerLock($repository, $key);
    $this->app->call([$collectComposerLock, 'handle']);
});

test('handle raises composer lock changed for the repository when its file is gone', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $repository = Repository::factory()->create(['composer_lock_oid' => null]);

    $collectComposerLock = new CollectComposerLock($repository, 'github');
    $this->app->call([$collectComposerLock, 'handle']);

    $eventFake->assertDispatched(ComposerLockChanged::class, static function (ComposerLockChanged $composerLockChanged) use ($repository): bool {
        return $composerLockChanged->repository->is($repository);
    });
});
