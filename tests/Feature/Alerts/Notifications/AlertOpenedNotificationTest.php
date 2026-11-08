<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedMailable;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedNotification;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;

test('via is the channels the user subscribed to for the type of the alert', function (): void {
    $alert = Alert::factory()->create();
    $alertType = $this->app->make(AlertTypes::class)->alertType($alert->alert_type_identifier);
    $notificationSubscription = NotificationSubscription::factory()->create([
        'alert_type_identifier' => $alert->alert_type_identifier,
    ]);

    $alertOpenedNotification = new AlertOpenedNotification($alert, $alertType);
    $channels = $alertOpenedNotification->via($notificationSubscription->user);

    expect($channels)
        ->toBe([$notificationSubscription->channel->value]);
});

test('via leaves out the channels the user subscribed to for another alert type', function (): void {
    $alertTypes = $this->app->make(AlertTypes::class);
    [$alertTypeIdentifier, $otherAlertTypeIdentifier] = fake()->randomElements($alertTypes->identifiers(), 2);
    $alert = Alert::factory()->create(['alert_type_identifier' => $alertTypeIdentifier]);
    $notificationSubscription = NotificationSubscription::factory()->create([
        'alert_type_identifier' => $otherAlertTypeIdentifier,
    ]);

    $alertOpenedNotification = new AlertOpenedNotification($alert, $alertTypes->alertType($alertTypeIdentifier));
    $channels = $alertOpenedNotification->via($notificationSubscription->user);

    expect($channels)
        ->toBeEmpty();
});

test('to mail is the mail of the alert to the email of the user', function (): void {
    $alert = Alert::factory()->create();
    $alertType = $this->app->make(AlertTypes::class)->alertType($alert->alert_type_identifier);
    $user = User::factory()->create();

    $alertOpenedNotification = new AlertOpenedNotification($alert, $alertType);
    $alertOpenedMailable = $alertOpenedNotification->toMail($user);

    expect($alertOpenedMailable)
        ->toBeInstanceOf(AlertOpenedMailable::class)
        ->and($alertOpenedMailable->alert->is($alert))
        ->toBeTrue()
        ->and($alertOpenedMailable->alertType)
        ->toBe($alertType)
        ->and($alertOpenedMailable->hasTo($user->email))
        ->toBeTrue();
});
