<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\SiteClientForm;

test('update url is the client update action of the site', function (): void {
    $site = Site::factory()->create();

    $siteClientForm = $this->app->make(SiteClientForm::class, [
        'site' => $site,
        'clientKey' => fake()->optional()->sha1(),
        'clientPath' => fake()->optional()->slug(),
    ]);

    expect($siteClientForm->updateUrl)
        ->toBe(route(RouteName::FORGE_SITE_CLIENT_UPDATE, $site));
});

test('cancel url is the page of the site', function (): void {
    $site = Site::factory()->create();

    $siteClientForm = $this->app->make(SiteClientForm::class, [
        'site' => $site,
        'clientKey' => fake()->optional()->sha1(),
        'clientPath' => fake()->optional()->slug(),
    ]);

    expect($siteClientForm->cancelUrl)
        ->toBe(route(RouteName::FORGE_SITE, $site));
});
