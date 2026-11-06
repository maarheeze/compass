<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteRow;

test('render renders the site row view', function (): void {
    $site = Site::factory()->create();

    $siteRow = $this->app->make(SiteRow::class, ['site' => $site]);

    expect($siteRow->render()->name())
        ->toBe('compass::components.site-row');
});

test('url is the compass page of the site', function (): void {
    $site = Site::factory()->create();

    $siteRow = $this->app->make(SiteRow::class, ['site' => $site]);

    expect($siteRow->url)
        ->toBe(route(RouteName::SITE, $site));
});
