<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\SiteBehindAlert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\AlertItem;

test('renders the view of the alert', function (): void {
    $alert = new SiteBehindAlert(Site::factory()->make());

    $alertItem = $this->app->make(AlertItem::class, ['alert' => $alert]);

    expect($alertItem->render()->name())
        ->toBe($alert->viewName());
});
