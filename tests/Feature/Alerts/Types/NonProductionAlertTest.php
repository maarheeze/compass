<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\NonProductionAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;

test('description names the site', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->nonProduction()
        ->for($site, 'subject')
        ->create();

    $nonProductionAlert = $this->app->make(NonProductionAlert::class);
    $description = $nonProductionAlert->description($alert);

    expect($description)
        ->toBe($site->name);
});
