<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Notifications\AlertOpenedMailable;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;

test('build sets the subject to the alert type on its subject', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();

    $alertOpenedMailable = new AlertOpenedMailable($alert, new SiteEndpointDown());

    $alertOpenedMailable->assertHasSubject(sprintf(
        'Endpoint down on %s%s',
        $siteEndpoint->siteReport->site->name,
        $siteEndpoint->path->toString(),
    ));
});

test('build says in its text that the alert opened on its subject', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->for($siteEndpoint, 'subject')
        ->create();

    $alertOpenedMailable = new AlertOpenedMailable($alert, new SiteEndpointDown());

    $alertOpenedMailable->assertSeeInText(sprintf(
        'A new alert opened: Endpoint down on %s%s.',
        $siteEndpoint->siteReport->site->name,
        $siteEndpoint->path->toString(),
    ));
});
