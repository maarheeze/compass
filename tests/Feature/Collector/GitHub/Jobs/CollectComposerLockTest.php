<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
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

    $collectComposerLock = new CollectComposerLock($repository);
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

    $collectComposerLock = new CollectComposerLock($repository);
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

    $collectComposerLock = new CollectComposerLock($repository);
    $this->app->call([$collectComposerLock, 'handle']);

    $this->assertDatabaseMissing(ComposerLock::class, ['id' => $composerLock->id]);
});
