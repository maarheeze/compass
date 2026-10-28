<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\MissingSiteAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;

test('description names the site', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->missingSite()
        ->for($site, 'subject')
        ->create();

    $missingSiteAlert = $this->app->make(MissingSiteAlert::class);
    $description = $missingSiteAlert->description($alert);

    expect($description)
        ->toBe($site->name);
});
