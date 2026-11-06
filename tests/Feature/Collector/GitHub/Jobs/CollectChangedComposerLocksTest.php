<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerLocks;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerLock;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('handle adds collect composer lock when its oid differs from the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_lock_version' => fake()->unique()->sha1(),
    ]);
    ComposerLock::factory()->create([
        'repository_id' => $repository->id,
        'version' => fake()->unique()->sha1(),
    ]);

    [$collectChangedComposerLocks, $batchFake] = new CollectChangedComposerLocks(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectComposerLock $collectComposerLock): Uuid {
            return $collectComposerLock->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toEqual([$repository->id]);
});

test('handle adds collect composer lock when the composer lock is gone', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_lock_version' => null,
    ]);
    ComposerLock::factory()->create(['repository_id' => $repository->id]);

    [$collectChangedComposerLocks, $batchFake] = new CollectChangedComposerLocks(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    $addedRepositoryIds = array_map(
        static function (CollectComposerLock $collectComposerLock): Uuid {
            return $collectComposerLock->repository->id;
        },
        $batchFake->added,
    );

    expect($addedRepositoryIds)
        ->toEqual([$repository->id]);
});

test('handle does not add collect composer lock when its oid is the collected one', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create([
        'run_id' => $runId,
        'composer_lock_version' => fake()->sha1(),
    ]);
    ComposerLock::factory()->create([
        'repository_id' => $repository->id,
        'version' => $repository->composer_lock_version,
    ]);

    [$collectChangedComposerLocks, $batchFake] = new CollectChangedComposerLocks(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle does not add collect composer lock for a repository without one', function (): void {
    $runId = Uuid::generate();
    Repository::factory()->create([
        'run_id' => $runId,
        'composer_lock_version' => null,
    ]);

    [$collectChangedComposerLocks, $batchFake] = new CollectChangedComposerLocks(
        $runId,
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle ignores repositories of another run', function (): void {
    Repository::factory()->create([
        'run_id' => Uuid::generate(),
        'composer_lock_version' => fake()->sha1(),
    ]);

    [$collectChangedComposerLocks, $batchFake] = new CollectChangedComposerLocks(
        Uuid::generate(),
        fake()->slug(),
    )->withFakeBatch();
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle adds collect composer lock for the source of its run', function (): void {
    $runId = Uuid::generate();
    $source = fake()->slug();
    Repository::factory()->create([
        'run_id' => $runId,
        'composer_lock_version' => fake()->sha1(),
    ]);

    [$collectChangedComposerLocks, $batchFake] = new CollectChangedComposerLocks($runId, $source)->withFakeBatch();
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    expect($batchFake->added[0]->source)
        ->toBe($source);
});
