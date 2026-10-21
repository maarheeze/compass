<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerJson;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('handle queries the blob of the current composer json oid', function (): void {
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

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);

    $http->assertSent(static function (HttpRequest $request) use ($repository): bool {
        if ($request['variables']['name'] !== $repository->name) {
            return false;
        }

        return $request['variables']['oid'] === $repository->composer_json_oid;
    });
});

test('handle stores the composer json with its oid', function (): void {
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

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);

    $this->assertDatabaseHas(ComposerJson::class, [
        'repository_id' => $repository->id,
        'oid' => $repository->composer_json_oid,
        'type' => $type,
    ]);
});

test('handle removes the composer json when the repository no longer has one', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => null]);
    $composerJson = ComposerJson::factory()->create(['repository_id' => $repository->id]);

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);

    $this->assertDatabaseMissing(ComposerJson::class, ['id' => $composerJson->id]);
});
