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
