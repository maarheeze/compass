<?php

declare(strict_types=1);

use Illuminate\Contracts\Container\Container;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Log\Context\Repository as Context;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Dependabot\DependabotFilesCollectorManager;
use Maarheeze\Compass\Collector\Dependabot\Jobs\CollectDependabotYml;
use Maarheeze\Compass\Collector\Events\DependabotYmlChanged;
use Maarheeze\Compass\Collector\SourceConfig;
use Maarheeze\Compass\Models\DependabotYml;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\RecordingRepositoryFilesCollector;
use Symfony\Component\Yaml\Yaml;

test('handle asks the collector of the source for the dependabot yml at the stored version', function (): void {
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => $driverName,
        ],
    ]);
    $recordingRepositoryFilesCollector = new RecordingRepositoryFilesCollector(Yaml::dump(['version' => 2]));
    $dependabotFilesCollectorManager = $this->app->make(DependabotFilesCollectorManager::class);
    $dependabotFilesCollectorManager->extend(
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
        'dependabot_yml_version' => fake()->sha1(),
    ]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    expect($recordingRepositoryFilesCollector->requestedFiles)
        ->toBe([
            [
                'repositoryName' => $repository->name,
                'version' => $repository->dependabot_yml_version,
            ],
        ]);
});

test('handle stores the package ecosystems of the dependabot yml with its version', function (): void {
    $packageEcosystem = fake()->randomElement(['composer', 'docker', 'github-actions', 'npm']);
    $source = fake()->slug();
    $driverName = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $source => [
            'driver' => $driverName,
        ],
    ]);
    $recordingRepositoryFilesCollector = new RecordingRepositoryFilesCollector(Yaml::dump([
        'version' => 2,
        'updates' => [
            [
                'package-ecosystem' => $packageEcosystem,
                'directory' => '/',
            ],
        ],
    ]));
    $dependabotFilesCollectorManager = $this->app->make(DependabotFilesCollectorManager::class);
    $dependabotFilesCollectorManager->extend(
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
        'dependabot_yml_version' => fake()->sha1(),
    ]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    $this->assertDatabaseHas(DependabotYml::class, [
        'repository_id' => $repository->id,
        'version' => $repository->dependabot_yml_version,
        'package_ecosystems' => json_encode([$packageEcosystem], JSON_THROW_ON_ERROR),
    ]);
});

test('handle removes the dependabot yml when the repository no longer has one', function (): void {
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
        'dependabot_yml_version' => null,
    ]);
    $dependabotYml = DependabotYml::factory()->create(['repository_id' => $repository->id]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    $this->assertDatabaseMissing(DependabotYml::class, ['id' => $dependabotYml->id]);
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
        'dependabot_yml_version' => null,
    ]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    expect($this->app->make(Context::class)->get('repository'))
        ->toBe($repository->name);
});

test('handle refuses a source without a Dependabot files collector', function (): void {
    $key = fake()->slug();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => fake()->slug(),
        ],
    ]);
    $repository = Repository::factory()->create();

    $this->expectException(InvalidArgumentException::class);

    $collectDependabotYml = new CollectDependabotYml($repository, $key);
    $this->app->call([$collectDependabotYml, 'handle']);
});

test('handle raises dependabot yml changed for the repository when its file is gone', function (): void {
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
    $repository = Repository::factory()->create(['dependabot_yml_version' => null]);

    $collectDependabotYml = new CollectDependabotYml($repository, $source);
    $this->app->call([$collectDependabotYml, 'handle']);

    $eventFake->assertDispatched(DependabotYmlChanged::class, static function (DependabotYmlChanged $dependabotYmlChanged) use ($repository): bool {
        return $dependabotYmlChanged->repository->is($repository);
    });
});

test('lock key is the repository', function (): void {
    $repository = Repository::factory()->create();

    $collectDependabotYml = new CollectDependabotYml($repository, fake()->slug());
    $lockKey = $collectDependabotYml->lockKey();

    expect($lockKey)
        ->toBe($repository->id->toString());
});
