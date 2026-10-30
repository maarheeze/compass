<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\User;

test('not done keeps an open alert', function (): void {
    $openAlert = Alert::factory()
        ->active()
        ->create();

    $notDoneAlerts = Alert::query()
        ->notDone()
        ->get();

    expect($notDoneAlerts->sole()->is($openAlert))
        ->toBeTrue();
});

test('not done leaves out a done alert', function (): void {
    Alert::factory()
        ->done()
        ->create();

    $notDoneAlerts = Alert::query()
        ->notDone()
        ->get();

    expect($notDoneAlerts)
        ->toBeEmpty();
});

test('of site keeps an alert of the site', function (): void {
    $site = Site::factory()->create();
    $siteAlert = Alert::factory()
        ->debugEnabled()
        ->for($site, 'subject')
        ->create();

    $siteAlerts = Alert::query()
        ->ofSite($site)
        ->get();

    expect($siteAlerts->sole()->is($siteAlert))
        ->toBeTrue();
});

test('of site keeps an alert of an endpoint of the site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $siteEndpointAlert = Alert::factory()
        ->downSiteEndpoint()
        ->for($siteEndpoint, 'subject')
        ->create();

    $siteAlerts = Alert::query()
        ->ofSite($siteEndpoint->site)
        ->get();

    expect($siteAlerts->sole()->is($siteEndpointAlert))
        ->toBeTrue();
});

test('of site leaves out the alerts of another site and its endpoints', function (): void {
    Alert::factory()
        ->debugEnabled()
        ->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->create();

    $siteAlerts = Alert::query()
        ->ofSite(Site::factory()->create())
        ->get();

    expect($siteAlerts)
        ->toBeEmpty();
});

test('assigned keeps an assigned alert', function (): void {
    $assignedAlert = Alert::factory()->create(['assignee_id' => User::factory()]);

    $assignedAlerts = Alert::query()
        ->assigned()
        ->get();

    expect($assignedAlerts->sole()->is($assignedAlert))
        ->toBeTrue();
});

test('assigned leaves out an unassigned alert', function (): void {
    Alert::factory()->create(['assignee_id' => null]);

    $assignedAlerts = Alert::query()
        ->assigned()
        ->get();

    expect($assignedAlerts)
        ->toBeEmpty();
});

test('done keeps a done alert', function (): void {
    $doneAlert = Alert::factory()
        ->done()
        ->create();

    $doneAlerts = Alert::query()
        ->done()
        ->get();

    expect($doneAlerts->sole()->is($doneAlert))
        ->toBeTrue();
});

test('done leaves out an open alert', function (): void {
    Alert::factory()
        ->active()
        ->create();

    $doneAlerts = Alert::query()
        ->done()
        ->get();

    expect($doneAlerts)
        ->toBeEmpty();
});

test('not assigned to keeps an unassigned alert', function (): void {
    $unassignedAlert = Alert::factory()->create(['assignee_id' => null]);

    $alertsNotAssignedToUser = Alert::query()
        ->notAssignedTo(User::factory()->create())
        ->get();

    expect($alertsNotAssignedToUser->sole()->is($unassignedAlert))
        ->toBeTrue();
});

test('not assigned to keeps an alert assigned to another user', function (): void {
    $alertAssignedToAnotherUser = Alert::factory()->create(['assignee_id' => User::factory()]);

    $alertsNotAssignedToUser = Alert::query()
        ->notAssignedTo(User::factory()->create())
        ->get();

    expect($alertsNotAssignedToUser->sole()->is($alertAssignedToAnotherUser))
        ->toBeTrue();
});

test('not assigned to leaves out an alert assigned to the user', function (): void {
    $user = User::factory()->create();
    Alert::factory()->create(['assignee_id' => $user->id]);

    $alertsNotAssignedToUser = Alert::query()
        ->notAssignedTo($user)
        ->get();

    expect($alertsNotAssignedToUser)
        ->toBeEmpty();
});

test('of same subject as keeps an alert of the same subject', function (): void {
    $alert = Alert::factory()->create();

    $alertsOfSameSubject = Alert::query()
        ->ofSameSubjectAs($alert)
        ->get();

    expect($alertsOfSameSubject->sole()->is($alert))
        ->toBeTrue();
});

test('of same subject as leaves out an alert of another subject', function (): void {
    Alert::factory()->create();
    $alertOfAnotherSubject = Alert::factory()->create();

    $alertsOfSameSubject = Alert::query()
        ->ofSameSubjectAs($alertOfAnotherSubject)
        ->get();

    expect($alertsOfSameSubject->sole()->is($alertOfAnotherSubject))
        ->toBeTrue();
});

test('of type keeps an alert of the type', function (): void {
    $alert = Alert::factory()->create();

    $alertsOfType = Alert::query()
        ->ofType($alert->type)
        ->get();

    expect($alertsOfType->sole()->is($alert))
        ->toBeTrue();
});

test('of type leaves out an alert of another type', function (): void {
    Alert::factory()
        ->debugEnabled()
        ->create();

    $alertsOfType = Alert::query()
        ->ofType(AlertType::NON_PRODUCTION)
        ->get();

    expect($alertsOfType)
        ->toBeEmpty();
});
