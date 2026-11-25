<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubClient;
use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubRepositoriesCollector;
use Maarheeze\Compass\Collector\RequestBudget;

test('repositories yields the collected repositories of each page', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([
        'data' => [
            'organization' => [
                'repositories' => [
                    'pageInfo' => [
                        'hasNextPage' => true,
                        'endCursor' => fake()->sha1(),
                    ],
                    'nodes' => [],
                ],
            ],
        ],
    ]);
    $responseSequence->push([
        'data' => [
            'organization' => [
                'repositories' => [
                    'pageInfo' => [
                        'hasNextPage' => false,
                        'endCursor' => null,
                    ],
                    'nodes' => [],
                ],
            ],
        ],
    ]);
    $http->fake(['*' => $responseSequence]);

    $gitHubRepositoriesCollector = new GitHubRepositoriesCollector(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
    );
    $collectedRepositoriesPerPage = iterator_to_array($gitHubRepositoriesCollector->repositories());

    expect($collectedRepositoriesPerPage)
        ->toEqual([new CollectedRepositoryCollection(), new CollectedRepositoryCollection()]);
});

test('repositories throws collecting failed when github fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $gitHubRepositoriesCollector = new GitHubRepositoriesCollector(
        new GitHubClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
    );
    iterator_to_array($gitHubRepositoriesCollector->repositories());
})->throws(CollectingFailed::class);
