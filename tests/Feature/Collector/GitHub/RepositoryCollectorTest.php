<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\GitHub\RepositoryCollector;

test('collect dispatches the first page', function (): void {
    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectionRunId = CollectionRunId::generate();

    $repositoryCollector = $this->app->make(RepositoryCollector::class);
    $repositoryCollector->collect($collectionRunId);

    expect($busFake->dispatched(CollectRepositories::class)->all())
        ->toEqual([new CollectRepositories($collectionRunId, null)]);
});
