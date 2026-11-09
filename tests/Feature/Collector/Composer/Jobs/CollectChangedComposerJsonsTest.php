<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Composer\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\Composer\Jobs\CollectComposerJson;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('handle adds collect composer json when its version differs from the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_version' => fake()->unique()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'version' => fake()->unique()->sha1(),
    ]);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($runId, fake()->slug());
    [$collectChangedComposerJsons, $batchFake] = $collectChangedComposerJsons->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectComposerJson $collectComposerJson): Uuid {
            return $collectComposerJson->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toEqual([$repository->id]);
});

test('handle adds collect composer json when the composer json is gone', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_version' => null,
    ]);
    ComposerJson::factory()->create(['repository_id' => $repository->id]);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($runId, fake()->slug());
    [$collectChangedComposerJsons, $batchFake] = $collectChangedComposerJsons->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectComposerJson $collectComposerJson): Uuid {
            return $collectComposerJson->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toEqual([$repository->id]);
});

test('handle does not add collect composer json when its version is the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_version' => fake()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'version' => $repository->composer_json_version,
    ]);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($runId, fake()->slug());
    [$collectChangedComposerJsons, $batchFake] = $collectChangedComposerJsons->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle does not add collect composer json for a repository without one', function (): void {
    $runId = Uuid::generate();
    Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_version' => null,
    ]);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($runId, fake()->slug());
    [$collectChangedComposerJsons, $batchFake] = $collectChangedComposerJsons->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle ignores repositories of another run', function (): void {
    Repository::factory()->create([
        'run_id' => Uuid::generate(),
        'composer_json_version' => fake()->sha1(),
    ]);

    $collectChangedComposerJsons = new CollectChangedComposerJsons(Uuid::generate(), fake()->slug());
    [$collectChangedComposerJsons, $batchFake] = $collectChangedComposerJsons->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('lock key is the source', function (): void {
    $source = fake()->slug();

    $collectChangedComposerJsons = new CollectChangedComposerJsons(Uuid::generate(), $source);
    $lockKey = $collectChangedComposerJsons->lockKey();

    expect($lockKey)
        ->toBe($source);
});
