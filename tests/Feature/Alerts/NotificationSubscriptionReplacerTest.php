<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Alerts\NotificationSubscriptionReplacer;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;

test('replace stores the notification subscriptions of the user', function (): void {
    $user = User::factory()->create();
    $alertTypeIdentifier = fake()->randomElement($this->app->make(AlertTypes::class)->identifiers());
    $notificationChannel = fake()->randomElement(NotificationChannel::cases());

    $notificationSubscriptionReplacer = $this->app->make(NotificationSubscriptionReplacer::class);
    $notificationSubscriptionReplacer->replace($user, new NotificationSubscriptionCollection([
        new NotificationSubscription([
            'alert_type_identifier' => $alertTypeIdentifier,
            'channel' => $notificationChannel,
        ]),
    ]));

    $this->assertDatabaseHas(NotificationSubscription::class, [
        'user_id' => $user->id,
        'alert_type_identifier' => $alertTypeIdentifier,
        'channel' => $notificationChannel,
    ]);
});

test('replace removes the previous notification subscriptions of the user', function (): void {
    $user = User::factory()->create();
    $previousNotificationSubscription = NotificationSubscription::factory()
        ->for($user)
        ->create();

    $notificationSubscriptionReplacer = $this->app->make(NotificationSubscriptionReplacer::class);
    $notificationSubscriptionReplacer->replace($user, new NotificationSubscriptionCollection());

    $this->assertModelMissing($previousNotificationSubscription);
});

test('replace keeps the notification subscriptions of other users', function (): void {
    $otherNotificationSubscription = NotificationSubscription::factory()->create();

    $notificationSubscriptionReplacer = $this->app->make(NotificationSubscriptionReplacer::class);
    $notificationSubscriptionReplacer->replace(User::factory()->create(), new NotificationSubscriptionCollection());

    $this->assertModelExists($otherNotificationSubscription);
});
