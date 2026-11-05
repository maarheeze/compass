<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\BuiltInDriver;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerJson;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('handle queries the blob of the current composer json oid', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => json_encode(['type' => fake()->word()], JSON_THROW_ON_ERROR),
                    ],
                ],
            ],
        ]),
    ]);

    $collectComposerJson = new CollectComposerJson($repository, $source);
    $this->app->call([$collectComposerJson, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($repository): bool {
        if ($request['variables']['name'] !== $repository->name) {
            return false;
        }

        return $request['variables']['oid'] === $repository->composer_json_oid;
    });
});

test('handle stores the composer json with its oid', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    $type = fake()->word();
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                'repository' => [
                    'object' => [
                        'text' => json_encode(['type' => $type], JSON_THROW_ON_ERROR),
                    ],
                ],
            ],
        ]),
    ]);

    $collectComposerJson = new CollectComposerJson($repository, $source);
    $this->app->call([$collectComposerJson, 'handle']);

    $this->assertDatabaseHas(ComposerJson::class, [
        'repository_id' => $repository->id,
        'oid' => $repository->composer_json_oid,
        'type' => $type,
    ]);
});

test('handle removes the composer json when the repository no longer has one', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['composer_json_oid' => null]);
    $composerJson = ComposerJson::factory()->create(['repository_id' => $repository->id]);

    $collectComposerJson = new CollectComposerJson($repository, $source);
    $this->app->call([$collectComposerJson, 'handle']);

    $this->assertDatabaseMissing(ComposerJson::class, ['id' => $composerJson->id]);
});

test('handle adds the repository to the log context', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => BuiltInDriver::GITHUB,
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $collectComposerJson = new CollectComposerJson($repository, $source);
    $this->app->call([$collectComposerJson, 'handle']);

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

    $collectComposerJson = new CollectComposerJson($repository, $key);
    $this->app->call([$collectComposerJson, 'handle']);
});

test('handle raises composer json changed for the repository when its file is gone', function (): void {
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
    $repository = Repository::factory()->create(['composer_json_oid' => null]);

    $collectComposerJson = new CollectComposerJson($repository, $source);
    $this->app->call([$collectComposerJson, 'handle']);

    $eventFake->assertDispatched(ComposerJsonChanged::class, static function (ComposerJsonChanged $composerJsonChanged) use ($repository): bool {
        return $composerJsonChanged->repository->is($repository);
    });
});
