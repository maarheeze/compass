<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\View\Components\NotificationChannelCheckbox;

test('is chosen is true with a subscription for the alert type and channel', function (): void {
    $notificationSubscription = NotificationSubscription::factory()->create();

    $notificationChannelCheckbox = $this->app->make(NotificationChannelCheckbox::class, [
        'alertType' => $notificationSubscription->alert_type,
        'notificationChannel' => $notificationSubscription->channel,
        'notificationSubscriptions' => new NotificationSubscriptionCollection([$notificationSubscription]),
    ]);

    expect($notificationChannelCheckbox->isChosen)
        ->toBeTrue();
});

test('is chosen is false with a subscription for another alert type only', function (): void {
    [$alertType, $otherAlertType] = fake()->randomElements(AlertType::cases(), 2);
    $notificationSubscription = NotificationSubscription::factory()->create(['alert_type' => $otherAlertType]);

    $notificationChannelCheckbox = $this->app->make(NotificationChannelCheckbox::class, [
        'alertType' => $alertType,
        'notificationChannel' => $notificationSubscription->channel,
        'notificationSubscriptions' => new NotificationSubscriptionCollection([$notificationSubscription]),
    ]);

    expect($notificationChannelCheckbox->isChosen)
        ->toBeFalse();
});
