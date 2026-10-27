<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteClientEditLink;

test('url is the client edit page of the site', function (): void {
    $site = Site::factory()->create();

    $siteClientEditLink = $this->app->make(SiteClientEditLink::class, ['site' => $site]);

    expect($siteClientEditLink->url)
        ->toBe(route(RouteName::FORGE_SITE_CLIENT_EDIT, $site));
});
