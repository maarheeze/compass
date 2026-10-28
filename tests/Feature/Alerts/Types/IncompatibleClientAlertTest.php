<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\IncompatibleClientAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;

test('description names the site', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->incompatibleClient()
        ->for($site, 'subject')
        ->create();

    $incompatibleClientAlert = $this->app->make(IncompatibleClientAlert::class);
    $description = $incompatibleClientAlert->description($alert);

    expect($description)
        ->toBe($site->name);
});
