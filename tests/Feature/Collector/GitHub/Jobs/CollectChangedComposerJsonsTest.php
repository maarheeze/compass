<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectChangedComposerJsons;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectComposerJson;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('handle dispatches collect composer json when its oid differs from the collected one', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_json_oid' => fake()->unique()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'oid' => fake()->unique()->sha1(),
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($collectionRunId);
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    $busFake->assertDispatched(
        CollectComposerJson::class,
        static fn (CollectComposerJson $collectComposerJson): bool => $collectComposerJson->repository->is($repository),
    );
});

test('handle dispatches collect composer json when the composer json is gone', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_json_oid' => null,
    ]);
    ComposerJson::factory()->create(['repository_id' => $repository->id]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($collectionRunId);
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    $busFake->assertDispatched(
        CollectComposerJson::class,
        static fn (CollectComposerJson $collectComposerJson): bool => $collectComposerJson->repository->is($repository),
    );
});

test('handle does not dispatch collect composer json when its oid is the collected one', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_json_oid' => fake()->sha1(),
    ]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'oid' => $repository->composer_json_oid,
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($collectionRunId);
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($busFake->dispatched(CollectComposerJson::class))
        ->toBeEmpty();
});

test('handle does not dispatch collect composer json for a repository without one', function (): void {
    $collectionRunId = CollectionRunId::generate();
    Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'composer_json_oid' => null,
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerJsons = new CollectChangedComposerJsons($collectionRunId);
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($busFake->dispatched(CollectComposerJson::class))
        ->toBeEmpty();
});

test('handle ignores repositories of another collection run', function (): void {
    Repository::factory()->create([
        'collection_run_id' => CollectionRunId::generate(),
        'composer_json_oid' => fake()->sha1(),
    ]);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectChangedComposerJsons = new CollectChangedComposerJsons(CollectionRunId::generate());
    $this->app->call([$collectChangedComposerJsons, 'handle']);

    expect($busFake->dispatched(CollectComposerJson::class))
        ->toBeEmpty();
});
