<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\OverdueDeploymentAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;

test('description names the site', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->overdueDeployment()
        ->for($site, 'subject')
        ->create();

    $overdueDeploymentAlert = $this->app->make(OverdueDeploymentAlert::class);
    $description = $overdueDeploymentAlert->description($alert);

    expect($description)
        ->toBe($site->name);
});
