<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\ForgeSiteUrlFactory;
use Maarheeze\Compass\Models\Site;

test('create builds the forge page of the site from the organization, server slug and forge id', function (): void {
    $organization = fake()->slug();
    $this->setConfiguration('compass.forge_organization', $organization);

    $site = Site::factory()->create();

    $forgeSiteUrlFactory = $this->app->make(ForgeSiteUrlFactory::class);
    $forgeSiteUrl = $forgeSiteUrlFactory->create($site);

    expect($forgeSiteUrl)
        ->toBe(sprintf('https://forge.laravel.com/%s/%s/%s', $organization, $site->server_slug, $site->forge_id));
});
