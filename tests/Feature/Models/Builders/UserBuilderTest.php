<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;

test('subscribed to keeps a user subscribed to the alert type', function (): void {
    $notificationSubscription = NotificationSubscription::factory()->create();

    $usersSubscribedToAlertType = User::query()
        ->subscribedTo($notificationSubscription->alert_type)
        ->get();

    expect($usersSubscribedToAlertType->sole()->is($notificationSubscription->user))
        ->toBeTrue();
});

test('subscribed to leaves out a user subscribed to another alert type only', function (): void {
    [$alertType, $otherAlertType] = fake()->randomElements(AlertType::cases(), 2);
    NotificationSubscription::factory()->create(['alert_type' => $otherAlertType]);

    $usersSubscribedToAlertType = User::query()
        ->subscribedTo($alertType)
        ->get();

    expect($usersSubscribedToAlertType)
        ->toBeEmpty();
});

test('subscribed to leaves out a user without subscriptions', function (): void {
    User::factory()->create();

    $usersSubscribedToAlertType = User::query()
        ->subscribedTo(fake()->randomElement(AlertType::cases()))
        ->get();

    expect($usersSubscribedToAlertType)
        ->toBeEmpty();
});
