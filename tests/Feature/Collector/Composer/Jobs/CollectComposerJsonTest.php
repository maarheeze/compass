<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Composer\Jobs\CollectComposerJson;
use Maarheeze\Compass\Collector\Events\ComposerJsonChanged;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\RecordingRepositoryFilesCollector;

test('handle asks the collector of the source for the composer json at the stored version', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => $driverName,
        ],
    ]);
    $recordingRepositoryFilesCollector = new RecordingRepositoryFilesCollector(
        json_encode(['type' => fake()->word()], JSON_THROW_ON_ERROR),
    );
    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);
    $composerFilesCollectorManager->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($recordingRepositoryFilesCollector): RecordingRepositoryFilesCollector {
            return $recordingRepositoryFilesCollector;
        },
    );
    $repository = Repository::factory()->create([
        'source' => $source,
        'composer_json_version' => fake()->sha1(),
    ]);

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);

    expect($recordingRepositoryFilesCollector->requestedFiles)
        ->toBe([
            [
                'repositoryName' => $repository->name,
                'version' => $repository->composer_json_version,
            ],
        ]);
});

test('handle stores the composer json with its version', function (): void {
    $type = fake()->word();
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => $driverName,
        ],
    ]);
    $recordingRepositoryFilesCollector = new RecordingRepositoryFilesCollector(
        json_encode(['type' => $type], JSON_THROW_ON_ERROR),
    );
    $composerFilesCollectorManager = $this->app->make(ComposerFilesCollectorManager::class);
    $composerFilesCollectorManager->extend(
        $driverName,
        static function (
            Container $container,
            SourceConfig $sourceConfig,
        ) use ($recordingRepositoryFilesCollector): RecordingRepositoryFilesCollector {
            return $recordingRepositoryFilesCollector;
        },
    );
    $repository = Repository::factory()->create([
        'source' => $source,
        'composer_json_version' => fake()->sha1(),
    ]);

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);

    $this->assertDatabaseHas(ComposerJson::class, [
        'repository_id' => $repository->id,
        'version' => $repository->composer_json_version,
        'type' => $type,
    ]);
});

test('handle removes the composer json when the repository no longer has one', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create([
        'source' => $source,
        'composer_json_version' => null,
    ]);
    $composerJson = ComposerJson::factory()->create(['repository_id' => $repository->id]);

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);

    $this->assertDatabaseMissing(ComposerJson::class, ['id' => $composerJson->id]);
});

test('handle adds the repository to the log context', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $repository = Repository::factory()->create([
        'source' => $source,
        'composer_json_version' => null,
    ]);

    $collectComposerJson = new CollectComposerJson($repository);
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
    $repository = Repository::factory()->create(['source' => $key]);

    $this->expectException(InvalidArgumentException::class);

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);
});

test('handle raises composer json changed for the repository when its file is gone', function (): void {
    $source = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => 'github',
            'organization' => fake()->slug(),
            'token' => fake()->sha1(),
        ],
    ]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $repository = Repository::factory()->create([
        'source' => $source,
        'composer_json_version' => null,
    ]);

    $collectComposerJson = new CollectComposerJson($repository);
    $this->app->call([$collectComposerJson, 'handle']);

    $eventFake->assertDispatched(ComposerJsonChanged::class, static function (ComposerJsonChanged $composerJsonChanged) use ($repository): bool {
        return $composerJsonChanged->repository->is($repository);
    });
});

test('lock key is the repository', function (): void {
    $repository = Repository::factory()->create();

    $collectComposerJson = new CollectComposerJson($repository);
    $lockKey = $collectComposerJson->lockKey();

    expect($lockKey)
        ->toBe($repository->id->toString());
});
