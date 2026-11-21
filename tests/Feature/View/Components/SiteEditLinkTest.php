<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteEditLink;

test('url is the edit page of the site', function (): void {
    $site = Site::factory()->create();

    $siteEditLink = $this->app->make(SiteEditLink::class, ['site' => $site]);

    expect($siteEditLink->url)
        ->toBe(route(RouteName::SITE_EDIT, $site));
});
