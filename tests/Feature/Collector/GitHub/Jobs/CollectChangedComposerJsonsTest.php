<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerJson;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('handle adds collect composer json when its oid differs from the collected one', function (): void {
    $runId = RunId::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => fake()->unique()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'oid' => fake()->unique()->sha1(),
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons($runId)->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectComposerJson $collectComposerJson): int {
            return $collectComposerJson->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toBe([$repository->id]);
});

test('handle adds collect composer json when the composer json is gone', function (): void {
    $runId = RunId::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => null,
    ]);
    ComposerJson::factory()->create(['repository_id' => $repository->id]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons($runId)->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectComposerJson $collectComposerJson): int {
            return $collectComposerJson->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toBe([$repository->id]);
});

test('handle does not add collect composer json when its oid is the collected one', function (): void {
    $runId = RunId::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => fake()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'oid' => $repository->composer_json_oid,
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons($runId)->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle does not add collect composer json for a repository without one', function (): void {
    $runId = RunId::generate();
    Repository::factory()->create([
        'run_id' => $runId,
        'composer_json_oid' => null,
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons($runId)->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle ignores repositories of another run', function (): void {
    Repository::factory()->create([
        'run_id' => RunId::generate(),
        'composer_json_oid' => fake()->sha1(),
    ]);

    [$collectChangedComposerJsons, $batchFake] = new CollectChangedComposerJsons(RunId::generate())->withFakeBatch();
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});
