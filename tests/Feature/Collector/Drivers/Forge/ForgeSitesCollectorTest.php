<?php

declare(strict_types=1);

use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeSitesCollector;
use Maarheeze\Compass\Collector\RequestBudget;

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

    $forgeSitesCollector = new ForgeSitesCollector(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        '/.*/',
    );
    $collectedSitesPerPage = iterator_to_array($forgeSitesCollector->sites());

    expect($collectedSitesPerPage)
        ->toEqual([new CollectedSiteCollection(), new CollectedSiteCollection()]);
});

test('sites yields only the collected sites on servers matching the server pattern', function (): void {
    $serverId = fake()->numerify('####');
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('#######'),
                    'type' => 'sites',
                    'attributes' => [
                        'name' => fake()->domainName(),
                        'url' => fake()->url(),
                        'status' => fake()->slug(),
                        'php_version' => null,
                        'quick_deploy' => null,
                        'repository' => [
                            'url' => null,
                            'branch' => null,
                        ],
                    ],
                    'relationships' => [
                        'server' => [
                            'data' => [
                                'type' => 'servers',
                                'id' => $serverId,
                            ],
                        ],
                        'latestDeployment' => [
                            'data' => null,
                        ],
                    ],
                ],
            ],
            'included' => [
                [
                    'id' => $serverId,
                    'type' => 'servers',
                    'attributes' => [
                        'slug' => fake()->slug(),
                        'name' => fake()->slug(),
                        'ip_address' => null,
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $forgeSitesCollector = new ForgeSitesCollector(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        '/\A\z/',
    );

    expect($forgeSitesCollector->sites()->current())
        ->toEqual(new CollectedSiteCollection());
});

test('sites throws collecting failed when forge fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $forgeSitesCollector = new ForgeSitesCollector(
        new ForgeClient(
            $http,
            fake()->sha1(),
            new RequestBudget($this->app->make(RateLimiter::class), fake()->slug(), fake()->numberBetween(10, 100)),
        ),
        fake()->slug(),
        '/.*/',
    );
    iterator_to_array($forgeSitesCollector->sites());
})->throws(CollectingFailed::class);
