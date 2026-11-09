<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Composer\ComposerFilesCollectorManager;
use Maarheeze\Compass\Collector\Composer\Jobs\CollectComposerLock;
use Maarheeze\Compass\Collector\Events\ComposerLockChanged;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\RecordingRepositoryFilesCollector;

test('handle asks the collector of the source for the composer lock at the stored version', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => $driverName,
        ],
    ]);
    $recordingRepositoryFilesCollector = new RecordingRepositoryFilesCollector(json_encode([
        'packages' => [],
        'packages-dev' => [],
        'platform' => [],
    ], JSON_THROW_ON_ERROR));
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
        'composer_lock_version' => fake()->sha1(),
    ]);

    $collectComposerLock = new CollectComposerLock($repository);
    $this->app->call([$collectComposerLock, 'handle']);

    expect($recordingRepositoryFilesCollector->requestedFiles)
        ->toBe([
            [
                'repositoryName' => $repository->name,
                'version' => $repository->composer_lock_version,
            ],
        ]);
});

test('handle stores the composer lock with its version', function (): void {
    $phpConstraint = fake()->numerify('^#.#');
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => $driverName,
        ],
    ]);
    $recordingRepositoryFilesCollector = new RecordingRepositoryFilesCollector(json_encode([
        'packages' => [],
        'packages-dev' => [],
        'platform' => [
            'php' => $phpConstraint,
        ],
    ], JSON_THROW_ON_ERROR));
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
        'composer_lock_version' => fake()->sha1(),
    ]);

    $collectComposerLock = new CollectComposerLock($repository);
    $this->app->call([$collectComposerLock, 'handle']);

    $this->assertDatabaseHas(ComposerLock::class, [
        'repository_id' => $repository->id,
        'version' => $repository->composer_lock_version,
        'php_constraint' => $phpConstraint,
    ]);
});

test('handle removes the composer lock when the repository no longer has one', function (): void {
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
        'composer_lock_version' => null,
    ]);
    $composerLock = ComposerLock::factory()->create(['repository_id' => $repository->id]);

    $collectComposerLock = new CollectComposerLock($repository);
    $this->app->call([$collectComposerLock, 'handle']);

    $this->assertDatabaseMissing(ComposerLock::class, ['id' => $composerLock->id]);
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
        'composer_lock_version' => null,
    ]);

    $collectComposerLock = new CollectComposerLock($repository);
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
    $repository = Repository::factory()->create(['source' => $key]);

    $this->expectException(InvalidArgumentException::class);

    $collectComposerLock = new CollectComposerLock($repository);
    $this->app->call([$collectComposerLock, 'handle']);
});

test('handle raises composer lock changed for the repository when its file is gone', function (): void {
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
        'composer_lock_version' => null,
    ]);

    $collectComposerLock = new CollectComposerLock($repository);
    $this->app->call([$collectComposerLock, 'handle']);

    $eventFake->assertDispatched(ComposerLockChanged::class, static function (ComposerLockChanged $composerLockChanged) use ($repository): bool {
        return $composerLockChanged->repository->is($repository);
    });
});

test('lock key is the repository', function (): void {
    $repository = Repository::factory()->create();

    $collectComposerLock = new CollectComposerLock($repository);
    $lockKey = $collectComposerLock->lockKey();

    expect($lockKey)
        ->toBe($repository->id->toString());
});
