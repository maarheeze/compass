<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\ForgeSiteCollection;
use Maarheeze\Compass\Collector\Forge\ForgeDeployment;
use Maarheeze\Compass\Collector\Forge\SitesQuery;
use Maarheeze\Compass\Tests\Fixtures\ForgeSiteFactory;

test('pages request the sites of the forge organization', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.forge.organization', $organization);

    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = $this->app->make(SitesQuery::class);
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

    $sitesQuery = $this->app->make(SitesQuery::class);
    iterator_to_array($sitesQuery->pages());

    $http->assertSent(static function (HttpRequest $request) use ($nextCursor): bool {
        return $request->data() === [
            'include' => 'server,latestDeployment',
            'page[size]' => 100,
            'page[cursor]' => $nextCursor,
        ];
    });
});

test('pages hold the forge sites in the response', function (): void {
    $forgeSite = ForgeSiteFactory::make();
    $serverId = fake()->numerify('####');
    $deploymentId = fake()->numerify('####');
    $this->app->make(HttpFactory::class)->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => $forgeSite->forgeId,
                    'type' => 'sites',
                    'attributes' => [
                        'name' => $forgeSite->name,
                        'url' => $forgeSite->url,
                        'status' => $forgeSite->status->value,
                        'php_version' => $forgeSite->phpVersion,
                        'quick_deploy' => $forgeSite->autoDeploy,
                        'repository' => [
                            'url' => $forgeSite->repositoryUrl,
                            'branch' => $forgeSite->repositoryBranch,
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
                        'slug' => $forgeSite->serverSlug,
                        'name' => $forgeSite->serverName,
                        'ip_address' => $forgeSite->serverIpAddress,
                    ],
                ],
                [
                    'id' => $deploymentId,
                    'type' => 'deployments',
                    'attributes' => [
                        'commit' => [
                            'hash' => $forgeSite->latestDeployment?->commitHash,
                            'message' => $forgeSite->latestDeployment?->commitMessage,
                        ],
                        'status' => $forgeSite->latestDeployment?->status->value,
                        'started_at' => $forgeSite->latestDeployment?->startedAt->toIso8601String(),
                        'ended_at' => $forgeSite->latestDeployment?->endedAt?->toIso8601String(),
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $sitesQuery = $this->app->make(SitesQuery::class);
    $sitesQueryResult = $sitesQuery->pages()->current();

    expect($sitesQueryResult->forgeSites)
        ->toEqual(new ForgeSiteCollection([$forgeSite]));
});

test('pages hold no latest deployment for a site that was never deployed', function (): void {
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

    $sitesQuery = $this->app->make(SitesQuery::class);
    $sitesQueryResult = $sitesQuery->pages()->current();

    expect($sitesQueryResult->forgeSites->sole()->latestDeployment)
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

    $sitesQuery = $this->app->make(SitesQuery::class);
    $sitesQueryResult = $sitesQuery->pages()->current();

    expect($sitesQueryResult->forgeSites->sole()->latestDeployment)
        ->toBeInstanceOf(ForgeDeployment::class)
        ->endedAt->toBeNull();
});

test('pages leave out the sites of servers that do not match the server pattern', function (): void {
    $this->setConfiguration('compass.forge.server_pattern', sprintf('/^%s$/', fake()->unique()->sha1()));
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

    $sitesQuery = $this->app->make(SitesQuery::class);
    $sitesQueryResult = $sitesQuery->pages()->current();

    expect($sitesQueryResult->forgeSites)
        ->toBeEmpty();
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

    $sitesQuery = $this->app->make(SitesQuery::class);
    iterator_to_array($sitesQuery->pages());

    $http->assertSentCount(1);
});
