<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Notifications\AlertOpenedMailable;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Models\Alert;

test('build sets the subject to the alert type on its subject', function (): void {
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->create();

    $alertOpenedMailable = new AlertOpenedMailable($alert, new SiteEndpointDown());

    $alertOpenedMailable->assertHasSubject(sprintf('Endpoint down on %s', $alert->subject->displayName()));
});

test('build says in its text that the alert opened on its subject', function (): void {
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->create();

    $alertOpenedMailable = new AlertOpenedMailable($alert, new SiteEndpointDown());

    $alertOpenedMailable->assertSeeInText(sprintf('A new alert opened: Endpoint down on %s.', $alert->subject->displayName()));
});
