<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeSitesCollector;

test('sites yields the collected sites of each page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([
        'data' => [],
        'meta' => [
            'next_cursor' => fake()->sha1(),
        ],
    ]);
    $responseSequence->push([
        'data' => [],
        'meta' => [
            'next_cursor' => null,
        ],
    ]);
    $http->fake(['*' => $responseSequence]);

    $forgeSitesCollector = new ForgeSitesCollector(new ForgeClient($http, fake()->sha1()), fake()->slug());
    $collectedSitesPerPage = iterator_to_array($forgeSitesCollector->sites());

    expect($collectedSitesPerPage)
        ->toEqual([new CollectedSiteCollection(), new CollectedSiteCollection()]);
});

test('sites throws collecting failed when forge fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $forgeSitesCollector = new ForgeSitesCollector(new ForgeClient($http, fake()->sha1()), fake()->slug());
    iterator_to_array($forgeSitesCollector->sites());
})->throws(CollectingFailed::class);
