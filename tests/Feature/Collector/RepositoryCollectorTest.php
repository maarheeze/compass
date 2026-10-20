<?php

declare(strict_types=1);

use Illuminate\Contracts\Bus\Dispatcher;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Jobs\CollectRepositories;
use Maarheeze\Compass\Collector\RepositoryCollector;

test('collect dispatches the first page of the organization', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.organization', $organization);

    $busFake = new BusFake($this->app->make(QueueingDispatcher::class));
    $this->app->instance(Dispatcher::class, $busFake);

    $collectionRunId = CollectionRunId::generate();

    $repositoryCollector = $this->app->make(RepositoryCollector::class);
    $repositoryCollector->collect($collectionRunId);

    expect($busFake->dispatched(CollectRepositories::class)->all())
        ->toEqual([new CollectRepositories(sprintf('orgs/%s/repos?per_page=100', $organization), $collectionRunId)]);
});
