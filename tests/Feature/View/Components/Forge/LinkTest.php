<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\ForgeSiteUrlFactory;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\Forge\Link;

test('url is the forge page of the site', function (): void {
    $site = Site::factory()->create();
    $forgeSiteUrlFactory = $this->app->make(ForgeSiteUrlFactory::class);

    $link = $this->app->make(Link::class, ['site' => $site]);

    expect($link->url)
        ->toBe($forgeSiteUrlFactory->create($site));
});
