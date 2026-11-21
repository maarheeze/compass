<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteForm;

test('update url is the update action of the site', function (): void {
    $site = Site::factory()->create();

    $siteForm = $this->app->make(SiteForm::class, [
        'site' => $site,
        'key' => fake()->optional()->sha1(),
    ]);

    expect($siteForm->updateUrl)
        ->toBe(route(RouteName::SITE_UPDATE, $site));
});

test('cancel url is the page of the site', function (): void {
    $site = Site::factory()->create();

    $siteForm = $this->app->make(SiteForm::class, [
        'site' => $site,
        'key' => fake()->optional()->sha1(),
    ]);

    expect($siteForm->cancelUrl)
        ->toBe(route(RouteName::SITE, $site));
});
