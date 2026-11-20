<?php

declare(strict_types=1);

use Illuminate\Http\Client\Factory as HttpFactory;
use Maarheeze\Compass\Collector\CollectingFailed;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeClient;
use Maarheeze\Compass\Collector\Drivers\Forge\ForgeSiteDomainsCollector;
use Maarheeze\Compass\Models\Site;

test('site domains throws collecting failed when forge fails', function (): void {
    $http = $this->app->make(HttpFactory::class);
    $http->fake(['*' => HttpFactory::response(null, 500)]);

    $forgeSiteDomainsCollector = new ForgeSiteDomainsCollector(new ForgeClient($http, fake()->sha1()), fake()->slug());
    $forgeSiteDomainsCollector->siteDomains(Site::factory()->create());
})->throws(CollectingFailed::class);
