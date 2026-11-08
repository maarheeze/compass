<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Models\NotificationSubscription;

test('has subscription is true for the alert type and channel of a subscription', function (): void {
    $notificationSubscription = NotificationSubscription::factory()->create();
    $alertType = $this->app->make(AlertTypes::class)->alertType($notificationSubscription->alert_type_identifier);
    $notificationSubscriptions = new NotificationSubscriptionCollection([$notificationSubscription]);

    $hasSubscription = $notificationSubscriptions->hasSubscription(
        $alertType,
        $notificationSubscription->channel,
    );

    expect($hasSubscription)
        ->toBeTrue();
});

test('has subscription is false for another alert type', function (): void {
    $alertTypes = $this->app->make(AlertTypes::class);
    [$alertTypeIdentifier, $otherAlertTypeIdentifier] = fake()->randomElements($alertTypes->identifiers(), 2);
    $notificationSubscription = NotificationSubscription::factory()->create([
        'alert_type_identifier' => $alertTypeIdentifier,
    ]);
    $notificationSubscriptions = new NotificationSubscriptionCollection([$notificationSubscription]);

    $hasSubscription = $notificationSubscriptions->hasSubscription(
        $alertTypes->alertType($otherAlertTypeIdentifier),
        $notificationSubscription->channel,
    );

    expect($hasSubscription)
        ->toBeFalse();
});

test('notification channels are the channels of the subscriptions to the alert type', function (): void {
    $notificationSubscription = NotificationSubscription::factory()->create();
    $alertType = $this->app->make(AlertTypes::class)->alertType($notificationSubscription->alert_type_identifier);
    $notificationSubscriptions = new NotificationSubscriptionCollection([$notificationSubscription]);

    $notificationChannels = $notificationSubscriptions->notificationChannels($alertType);

    expect($notificationChannels)
        ->toBe([$notificationSubscription->channel]);
});

test('notification channels leave out the subscriptions to another alert type', function (): void {
    $alertTypes = $this->app->make(AlertTypes::class);
    [$alertTypeIdentifier, $otherAlertTypeIdentifier] = fake()->randomElements($alertTypes->identifiers(), 2);
    $notificationSubscriptions = new NotificationSubscriptionCollection([
        NotificationSubscription::factory()->create(['alert_type_identifier' => $otherAlertTypeIdentifier]),
    ]);
    $alertType = $alertTypes->alertType($alertTypeIdentifier);

    $notificationChannels = $notificationSubscriptions->notificationChannels($alertType);

    expect($notificationChannels)
        ->toBeEmpty();
});
