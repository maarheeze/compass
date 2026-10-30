<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Alerts\NotificationSubscriptionReplacer;
use Maarheeze\Compass\Collections\NotificationSubscriptionCollection;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;

test('replace stores the notification subscriptions of the user', function (): void {
    $user = User::factory()->create();
    $alertType = fake()->randomElement(AlertType::cases());
    $notificationChannel = fake()->randomElement(NotificationChannel::cases());

    $notificationSubscriptionReplacer = $this->app->make(NotificationSubscriptionReplacer::class);
    $notificationSubscriptionReplacer->replace($user, new NotificationSubscriptionCollection([
        new NotificationSubscription(['alert_type' => $alertType, 'channel' => $notificationChannel]),
    ]));

    $this->assertDatabaseHas(NotificationSubscription::class, [
        'user_id' => $user->id,
        'alert_type' => $alertType,
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
