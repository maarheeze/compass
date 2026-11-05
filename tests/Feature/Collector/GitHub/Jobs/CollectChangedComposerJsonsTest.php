<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerJson;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('handle adds collect composer json when its oid differs from the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => fake()->unique()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'oid' => fake()->unique()->sha1(),
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
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
        'composer_json_oid' => null,
    ]);
    ComposerJson::factory()->create(['repository_id' => $repository->id]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
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

test('handle does not add collect composer json when its oid is the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => fake()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'oid' => $repository->composer_json_oid,
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle does not add collect composer json for a repository without one', function (): void {
    $runId = Uuid::generate();
    Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => null,
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle ignores repositories of another run', function (): void {
    Repository::factory()->create([
        'run_id' => Uuid::generate(),
        'composer_json_oid' => fake()->sha1(),
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons(
        Uuid::generate(),
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle adds collect composer json for the source of its run', function (): void {
    $runId = Uuid::generate();
    $source = fake()->slug();
    Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => fake()->sha1(),
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons($runId, $source)->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added[0]->source)
        ->toBe($source);
});
