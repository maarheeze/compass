<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\Tests\Fixtures\CollectedSiteFactory;

test('pages request the sites of the forge organization', function (): void {
    $organization = fake()->slug();

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = new SitesQuery(new ForgeClient($http, fake()->sha1()), $organization);
    $sitesQuery->pages()->current();

    $http->assertSent(static function (HttpRequest $request) use ($organization): bool {
        return str_starts_with($request->url(), sprintf('https://forge.laravel.com/api/orgs/%s/sites?', $organization));
    });
});

test('pages request the next page with their server and latest deployment from the cursor of the page before it', function (): void {
    $nextCursor = fake()->sha1();

    $http = $this->app->make(HttpFactory::class);
    $responseSequence = $http->sequence();
    $responseSequence->push([
        'data' => [],
        'meta' => [
            'next_cursor' => $nextCursor,
        ],
    ]);
    $responseSequence->push([
        'data' => [],
        'meta' => [
            'next_cursor' => null,
        ],
    ]);
    $http->fake(['*' => $responseSequence]);

    $sitesQuery = new SitesQuery(new ForgeClient($http, fake()->sha1()), fake()->slug());
    iterator_to_array($sitesQuery->pages());

    $http->assertSent(static function (HttpRequest $request) use ($nextCursor): bool {
        return $request->data() === [
            'include' => 'server,latestDeployment',
            'page[size]' => 100,
            'page[cursor]' => $nextCursor,
        ];
    });
});

test('pages hold the collected sites in the response', function (): void {
    $collectedSite = CollectedSiteFactory::withDeploymentStatus(DeploymentStatus::SUCCEEDED);
    $serverId = fake()->numerify('####');
    $deploymentId = fake()->numerify('####');
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => $collectedSite->externalId,
                    'type' => 'sites',
                    'attributes' => [
                        'name' => $collectedSite->name,
                        'url' => $collectedSite->url,
                        'status' => fake()->slug(),
                        'php_version' => $collectedSite->phpVersion,
                        'quick_deploy' => $collectedSite->autoDeploy,
                        'repository' => [
                            'url' => $collectedSite->repositoryUrl,
                            'branch' => $collectedSite->repositoryBranch,
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
                            'data' => [
                                'type' => 'deployments',
                                'id' => $deploymentId,
                            ],
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
                        'name' => $collectedSite->serverName,
                        'ip_address' => $collectedSite->serverIpAddress,
                    ],
                ],
                [
                    'id' => $deploymentId,
                    'type' => 'deployments',
                    'attributes' => [
                        'commit' => [
                            'hash' => $collectedSite->deployedCommitHash,
                            'message' => fake()->sentence(),
                        ],
                        'status' => 'finished',
                        'started_at' => $collectedSite->deploymentStartedAt?->toIso8601String(),
                        'ended_at' => $collectedSite->deploymentEndedAt?->toIso8601String(),
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = new SitesQuery(new ForgeClient($http, fake()->sha1()), fake()->slug());
    $sitesQueryResult = $sitesQuery->pages()->current();

    expect($sitesQueryResult->collectedSites)
        ->toEqual(new CollectedSiteCollection([$collectedSite]));
});

test('pages map the deployment status of forge to the deployment status of the collected site', function (
    string $forgeDeploymentStatus,
    DeploymentStatus $deploymentStatus,
): void {
    $serverId = fake()->numerify('####');
    $deploymentId = fake()->numerify('####');
    $this->app->make(HttpFactory::class)->fake([
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
                            'data' => [
                                'type' => 'deployments',
                                'id' => $deploymentId,
                            ],
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
                        'ip_address' => fake()->ipv4(),
                    ],
                ],
                [
                    'id' => $deploymentId,
                    'type' => 'deployments',
                    'attributes' => [
                        'commit' => [
                            'hash' => fake()->sha1(),
                            'message' => fake()->sentence(),
                        ],
                        'status' => $forgeDeploymentStatus,
                        'started_at' => fake()->iso8601(),
                        'ended_at' => null,
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = new SitesQuery(new ForgeClient($this->app->make(HttpFactory::class), fake()->sha1()), fake()->slug());
    $sitesQueryResult = $sitesQuery->pages()->current();

    expect($sitesQueryResult->collectedSites->sole()->deploymentStatus)
        ->toBe($deploymentStatus);
})->with([
    'pending' => ['pending', DeploymentStatus::QUEUED],
    'queued' => ['queued', DeploymentStatus::QUEUED],
    'deploying' => ['deploying', DeploymentStatus::RUNNING],
    'finished' => ['finished', DeploymentStatus::SUCCEEDED],
    'failed' => ['failed', DeploymentStatus::FAILED],
    'failed build' => ['failed-build', DeploymentStatus::FAILED],
    'cancelled' => ['cancelled', DeploymentStatus::CANCELLED],
]);

test('pages hold no deployment for a site that was never deployed', function (): void {
    $serverId = fake()->numerify('####');
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('#######'),
                    'type' => 'sites',
                    'attributes' => [
                        'name' => fake()->domainName(),
                        'url' => fake()->url(),
                        'status' => 'never-deployed',
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
                        'ip_address' => fake()->ipv4(),
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = new SitesQuery(new ForgeClient($this->app->make(HttpFactory::class), fake()->sha1()), fake()->slug());
    $collectedSite = $sitesQuery->pages()->current()->collectedSites->sole();

    expect($collectedSite->deploymentStatus)
        ->toBeNull()
        ->and($collectedSite->deployedCommitHash)
        ->toBeNull()
        ->and($collectedSite->deploymentStartedAt)
        ->toBeNull()
        ->and($collectedSite->deploymentEndedAt)
        ->toBeNull();
});

test('pages hold no end time for a deployment that has not ended', function (): void {
    $serverId = fake()->numerify('####');
    $deploymentId = fake()->numerify('####');
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('#######'),
                    'type' => 'sites',
                    'attributes' => [
                        'name' => fake()->domainName(),
                        'url' => fake()->url(),
                        'status' => 'deploying',
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
                            'data' => [
                                'type' => 'deployments',
                                'id' => $deploymentId,
                            ],
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
                        'ip_address' => fake()->ipv4(),
                    ],
                ],
                [
                    'id' => $deploymentId,
                    'type' => 'deployments',
                    'attributes' => [
                        'commit' => [
                            'hash' => fake()->sha1(),
                            'message' => fake()->sentence(),
                        ],
                        'status' => 'deploying',
                        'started_at' => fake()->iso8601(),
                        'ended_at' => null,
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = new SitesQuery(new ForgeClient($this->app->make(HttpFactory::class), fake()->sha1()), fake()->slug());
    $sitesQueryResult = $sitesQuery->pages()->current();

    expect($sitesQueryResult->collectedSites->sole()->deploymentEndedAt)
        ->toBeNull();
});

test('pages end with the page without a next cursor', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = new SitesQuery(new ForgeClient($http, fake()->sha1()), fake()->slug());
    iterator_to_array($sitesQuery->pages());

    $http->assertSentCount(1);
});
