<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\SiteBehindAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;

test('description names the site', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->siteBehind()
        ->for($site, 'subject')
        ->create();

    $siteBehindAlert = $this->app->make(SiteBehindAlert::class);
    $description = $siteBehindAlert->description($alert);

    expect($description)
        ->toBe($site->name);
});
