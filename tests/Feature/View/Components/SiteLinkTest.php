<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteLink;

test('url is the compass page of the site', function (): void {
    $site = Site::factory()->create();

    $siteLink = $this->app->make(SiteLink::class, ['site' => $site]);

    expect($siteLink->url)
        ->toBe(route(RouteName::FORGE_SITE, $site));
});
