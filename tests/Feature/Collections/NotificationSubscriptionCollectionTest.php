<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Models\NotificationSubscription;

test('has subscription is true for the alert type and channel of a subscription', function (): void {
    $notificationSubscription = NotificationSubscription::factory()->create();
    $notificationSubscriptions = new NotificationSubscriptionCollection([$notificationSubscription]);

    $hasSubscription = $notificationSubscriptions->hasSubscription(
        $notificationSubscription->alert_type,
        $notificationSubscription->channel,
    );

    expect($hasSubscription)
        ->toBeTrue();
});

test('has subscription is false for another alert type', function (): void {
    [$alertType, $otherAlertType] = fake()->randomElements(AlertType::cases(), 2);
    $notificationSubscription = NotificationSubscription::factory()->create(['alert_type' => $alertType]);
    $notificationSubscriptions = new NotificationSubscriptionCollection([$notificationSubscription]);

    $hasSubscription = $notificationSubscriptions->hasSubscription($otherAlertType, $notificationSubscription->channel);

    expect($hasSubscription)
        ->toBeFalse();
});

test('notification channels are the channels of the subscriptions to the alert type', function (): void {
    $notificationSubscription = NotificationSubscription::factory()->create();
    $notificationSubscriptions = new NotificationSubscriptionCollection([$notificationSubscription]);

    $notificationChannels = $notificationSubscriptions->notificationChannels($notificationSubscription->alert_type);

    expect($notificationChannels)
        ->toBe([$notificationSubscription->channel]);
});

test('notification channels leave out the subscriptions to another alert type', function (): void {
    [$alertType, $otherAlertType] = fake()->randomElements(AlertType::cases(), 2);
    $notificationSubscriptions = new NotificationSubscriptionCollection([
        NotificationSubscription::factory()->create(['alert_type' => $otherAlertType]),
    ]);

    $notificationChannels = $notificationSubscriptions->notificationChannels($alertType);

    expect($notificationChannels)
        ->toBeEmpty();
});
