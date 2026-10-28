<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\UnauthorizedClientAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;

test('description names the site', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->unauthorizedClient()
        ->for($site, 'subject')
        ->create();

    $unauthorizedClientAlert = $this->app->make(UnauthorizedClientAlert::class);
    $description = $unauthorizedClientAlert->description($alert);

    expect($description)
        ->toBe($site->name);
});
