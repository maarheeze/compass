<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collections\CollectedSiteDomainCollection;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\SiteDomainsQuery;
use Maarheeze\Compass\Collector\SiteDomains\CollectedSiteDomain;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;
use Maarheeze\Compass\Models\Site;

test('site domains request the domains of the site on its server', function (): void {
    $organisation = fake()->slug();
    $site = Site::factory()->create();
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $siteDomainsQuery = new SiteDomainsQuery(new ForgeClient($http, fake()->sha1()), $organisation);
    $siteDomainsQuery->siteDomains($site);

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $site): bool {
        return str_starts_with($request->url(), sprintf(
            'https://forge.laravel.com/api/orgs/%s/servers/%s/sites/%s/domains?',
            $organisation,
            $site->server_external_id,
            $site->external_id,
        ));
    });
});

test('site domains hold the domains in the response', function (): void {
    $collectedSiteDomain = new CollectedSiteDomain(
        fake()->domainName(),
        fake()->randomElement(SiteDomainType::cases()),
        fake()->randomElement(SiteDomainStatus::cases()),
    );
    $http = $this->app->make(HttpFactory::class);
    $http->fake([
        '*' => HttpFactory::response([
            'data' => [
                [
                    'id' => fake()->numerify('####'),
                    'type' => 'domainRecords',
                    'attributes' => [
                        'name' => $collectedSiteDomain->name,
                        'type' => $collectedSiteDomain->type->value,
                        'status' => $collectedSiteDomain->status->value,
                    ],
                ],
            ],
            'meta' => [
                'next_cursor' => null,
            ],
        ]),
    ]);

    $siteDomainsQuery = new SiteDomainsQuery(new ForgeClient($http, fake()->sha1()), fake()->slug());
    $collectedSiteDomains = $siteDomainsQuery->siteDomains(Site::factory()->create());

    expect($collectedSiteDomains)
        ->toEqual(new CollectedSiteDomainCollection([$collectedSiteDomain]));
});

test('site domains request the next page from the cursor of the page before it', function (): void {
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

    $siteDomainsQuery = new SiteDomainsQuery(new ForgeClient($http, fake()->sha1()), fake()->slug());
    $siteDomainsQuery->siteDomains(Site::factory()->create());

    $http->assertSent(static function (HttpRequest $request) use ($nextCursor): bool {
        return $request->data() === [
            'page[size]' => 100,
            'page[cursor]' => $nextCursor,
        ];
    });
});
