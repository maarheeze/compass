<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;

test('subject is stored under its alias', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'subject_type' => 'site-endpoint',
        'subject_id' => $siteEndpoint->id,
    ]);
});
