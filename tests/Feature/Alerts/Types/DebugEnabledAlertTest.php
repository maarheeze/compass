<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\DebugEnabledAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;

test('description names the site', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->debugEnabled()
        ->for($site, 'subject')
        ->create();

    $debugEnabledAlert = $this->app->make(DebugEnabledAlert::class);
    $description = $debugEnabledAlert->description($alert);

    expect($description)
        ->toBe($site->name);
});
