<?php

declare(strict_types=1);

use Illuminate\Contracts\View\Factory as ViewFactory;
use Maarheeze\Compass\Analyzer\Forge\SiteBehindAlert;
use Maarheeze\Compass\Models\Site;

test('view name names an existing view', function (): void {
    $viewFactory = $this->app->make(ViewFactory::class);

    $alert = new SiteBehindAlert(Site::factory()->make());

    expect($viewFactory->exists($alert->viewName()))
        ->toBeTrue();
});
