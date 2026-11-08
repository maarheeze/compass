<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;

test('subscribed to keeps a user subscribed to the alert type', function (): void {
    $notificationSubscription = NotificationSubscription::factory()->create();
    $alertType = $this->app->make(AlertTypes::class)->alertType($notificationSubscription->alert_type_identifier);

    $usersSubscribedToAlertType = User::query()
        ->subscribedTo($alertType)
        ->get();

    expect($usersSubscribedToAlertType->sole()->is($notificationSubscription->user))
        ->toBeTrue();
});

test('subscribed to leaves out a user subscribed to another alert type only', function (): void {
    $alertTypes = $this->app->make(AlertTypes::class);
    [$alertTypeIdentifier, $otherAlertTypeIdentifier] = fake()->randomElements($alertTypes->identifiers(), 2);
    NotificationSubscription::factory()->create(['alert_type_identifier' => $otherAlertTypeIdentifier]);

    $usersSubscribedToAlertType = User::query()
        ->subscribedTo($alertTypes->alertType($alertTypeIdentifier))
        ->get();

    expect($usersSubscribedToAlertType)
        ->toBeEmpty();
});

test('subscribed to leaves out a user without subscriptions', function (): void {
    User::factory()->create();
    $alertTypes = $this->app->make(AlertTypes::class);
    $alertType = $alertTypes->alertType(fake()->randomElement($alertTypes->identifiers()));

    $usersSubscribedToAlertType = User::query()
        ->subscribedTo($alertType)
        ->get();

    expect($usersSubscribedToAlertType)
        ->toBeEmpty();
});
