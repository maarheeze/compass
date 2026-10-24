<?php

declare(strict_types=1);

use Illuminate\Contracts\View\Factory as ViewFactory;
use Maarheeze\Compass\Analyzer\SiteEndpoint\DownSiteEndpointAlert;
use Maarheeze\Compass\Models\SiteEndpointCheck;

test('view name names an existing view', function (): void {
    $viewFactory = $this->app->make(ViewFactory::class);

    $alert = new DownSiteEndpointAlert(SiteEndpointCheck::factory()->make());

    expect($viewFactory->exists($alert->viewName()))
        ->toBeTrue();
});
