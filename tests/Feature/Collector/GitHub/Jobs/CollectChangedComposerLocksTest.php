<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerLocks;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerLock;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\Repository;

test('handle dispatches collect composer lock when its oid differs from the collected one', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_lock_oid' => fake()->unique()->sha1(),
    ]);
    ComposerLock::factory()->create([
        'repository_id' => $repository->id,
        'oid' => fake()->unique()->sha1(),
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerLocks = new CollectChangedComposerLocks($collectionRunId);
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    $busFake->assertDispatched(
        CollectComposerLock::class,
        static function (CollectComposerLock $collectComposerLock) use ($repository): bool {
            return $collectComposerLock->repository->is($repository);
        },
    );
});

test('handle dispatches collect composer lock when the composer lock is gone', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_lock_oid' => null,
    ]);
    ComposerLock::factory()->create(['repository_id' => $repository->id]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerLocks = new CollectChangedComposerLocks($collectionRunId);
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    $busFake->assertDispatched(
        CollectComposerLock::class,
        static function (CollectComposerLock $collectComposerLock) use ($repository): bool {
            return $collectComposerLock->repository->is($repository);
        },
    );
});

test('handle does not dispatch collect composer lock when its oid is the collected one', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_lock_oid' => fake()->sha1(),
    ]);
    ComposerLock::factory()->create([
        'repository_id' => $repository->id,
        'oid' => $repository->composer_lock_oid,
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerLocks = new CollectChangedComposerLocks($collectionRunId);
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    expect($busFake->dispatched(CollectComposerLock::class))
        ->toBeEmpty();
});

test('handle does not dispatch collect composer lock for a repository without one', function (): void {
    $collectionRunId = CollectionRunId::generate();
    Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_lock_oid' => null,
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerLocks = new CollectChangedComposerLocks($collectionRunId);
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    expect($busFake->dispatched(CollectComposerLock::class))
        ->toBeEmpty();
});

test('handle ignores repositories of another collection run', function (): void {
    Repository::factory()->create([
        'collection_run_id' => CollectionRunId::generate(),
        'composer_lock_oid' => fake()->sha1(),
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerLocks = new CollectChangedComposerLocks(CollectionRunId::generate());
    $this->app->call([$collectChangedComposerLocks, 'handle']);

    expect($busFake->dispatched(CollectComposerLock::class))
        ->toBeEmpty();
});
