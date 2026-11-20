<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Illuminate\Http\Client\Request as HttpRequest;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainsCollectorManager;
use Maarheeze\Compass\Models\Site;

test('collector of a forge source collects the domains of a site of its organisation with its token', function (): void {
    $key = fake()->slug();
    $organisation = fake()->slug();
    $token = fake()->sha1();
    $this->setConfiguration('compass.sources', [
        $key => [
            'driver' => 'forge',
            'organisation' => $organisation,
            'token' => $token,
        ],
    ]);
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

    $siteDomainsCollectorManager = $this->app->make(SiteDomainsCollectorManager::class);
    $siteDomainsCollectorManager->collector($key)->siteDomains($site);

    $http->assertSent(static function (HttpRequest $request) use ($organisation, $token): bool {
        return str_starts_with($request->url(), sprintf('https://forge.laravel.com/api/orgs/%s/', $organisation))
            && $request->hasHeader('Authorization', sprintf('Bearer %s', $token));
    });
});
