<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Types\ComposerProjectWithoutLock;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\Repository;

test('applicable keeps a check that applies', function (): void {
    $applicableAlertCheck = AlertCheck::factory()->create([
        'condition' => fake()->randomElement([ConditionState::ACTIVE, ConditionState::RESOLVED]),
    ]);

    $applicableAlertChecks = AlertCheck::query()
        ->applicable()
        ->get();

    expect($applicableAlertChecks->sole()->is($applicableAlertCheck))
        ->toBeTrue();
});

test('applicable leaves out a check that does not apply', function (): void {
    AlertCheck::factory()->create(['condition' => ConditionState::NOT_APPLICABLE]);

    $applicableAlertChecks = AlertCheck::query()
        ->applicable()
        ->get();

    expect($applicableAlertChecks)
        ->toBeEmpty();
});

test('of subject keeps a check of the subject', function (): void {
    $alertCheck = AlertCheck::factory()->create();

    $alertChecksOfSubject = AlertCheck::query()
        ->ofSubject($alertCheck->subject)
        ->get();

    expect($alertChecksOfSubject->sole()->is($alertCheck))
        ->toBeTrue();
});

test('of subject leaves out a check of another subject', function (): void {
    AlertCheck::factory()->create();
    $repository = Repository::factory()->create();

    $alertChecksOfSubject = AlertCheck::query()
        ->ofSubject($repository)
        ->get();

    expect($alertChecksOfSubject)
        ->toBeEmpty();
});

test('with alert muted is true for a check whose open alert is muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();
    AlertCheck::factory()
        ->for($alert->subject, 'subject')
        ->create(['alert_type_identifier' => $alert->alert_type_identifier]);

    $alertCheck = AlertCheck::query()
        ->withAlertMuted()
        ->sole();

    expect($alertCheck->alert_is_muted)
        ->toBeTrue();
});

test('with alert muted is false for a check whose open alert is not muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['is_muted' => false]);
    AlertCheck::factory()
        ->for($alert->subject, 'subject')
        ->create(['alert_type_identifier' => $alert->alert_type_identifier]);

    $alertCheck = AlertCheck::query()
        ->withAlertMuted()
        ->sole();

    expect($alertCheck->alert_is_muted)
        ->toBeFalse();
});

test('with alert muted is null for a check without an alert', function (): void {
    AlertCheck::factory()->create();

    $alertCheck = AlertCheck::query()
        ->withAlertMuted()
        ->sole();

    expect($alertCheck->alert_is_muted)
        ->toBeNull();
});

test('with alert muted is null for a check whose alert is done', function (): void {
    $alert = Alert::factory()
        ->done()
        ->muted()
        ->create();
    AlertCheck::factory()
        ->for($alert->subject, 'subject')
        ->create(['alert_type_identifier' => $alert->alert_type_identifier]);

    $alertCheck = AlertCheck::query()
        ->withAlertMuted()
        ->sole();

    expect($alertCheck->alert_is_muted)
        ->toBeNull();
});

test('with alert muted is null for a check whose subject has an alert of another type', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->composerLibraryWithLock()
        ->create();
    AlertCheck::factory()
        ->for($alert->subject, 'subject')
        ->create(['alert_type_identifier' => new ComposerProjectWithoutLock()->identifier()]);

    $alertCheck = AlertCheck::query()
        ->withAlertMuted()
        ->sole();

    expect($alertCheck->alert_is_muted)
        ->toBeNull();
});

test('with alert muted is null for a check of a subject other than the alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->composerLibraryWithLock()
        ->create();
    AlertCheck::factory()
        ->for(Repository::factory(), 'subject')
        ->create(['alert_type_identifier' => $alert->alert_type_identifier]);

    $alertCheck = AlertCheck::query()
        ->withAlertMuted()
        ->sole();

    expect($alertCheck->alert_is_muted)
        ->toBeNull();
});
