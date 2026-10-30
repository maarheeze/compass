<?php

declare(strict_types=1);

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Testing\Fakes\NotificationFake;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Listeners\SendAlertNotifications;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedNotification;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\NotificationSubscription;

test('handle notifies a user subscribed to the type of the opened alert', function (): void {
    $notificationFake = new NotificationFake();
    $this->app->instance(Dispatcher::class, $notificationFake);
    $alert = Alert::factory()->create();
    $notificationSubscription = NotificationSubscription::factory()->create(['alert_type' => $alert->type]);

    $sendAlertNotifications = $this->app->make(SendAlertNotifications::class);
    $sendAlertNotifications->handle(new AlertOpened($alert));

    $notificationFake->assertSentTo(
        $notificationSubscription->user,
        AlertOpenedNotification::class,
        static function (AlertOpenedNotification $alertOpenedNotification) use ($alert): bool {
            return $alertOpenedNotification->alert->is($alert);
        },
    );
});

test('handle does not notify a user subscribed to another alert type only', function (): void {
    $notificationFake = new NotificationFake();
    $this->app->instance(Dispatcher::class, $notificationFake);
    [$alertType, $otherAlertType] = fake()->randomElements(AlertType::cases(), 2);
    $alert = Alert::factory()->create(['type' => $alertType]);
    $notificationSubscription = NotificationSubscription::factory()->create(['alert_type' => $otherAlertType]);

    $sendAlertNotifications = $this->app->make(SendAlertNotifications::class);
    $sendAlertNotifications->handle(new AlertOpened($alert));

    $notificationFake->assertNotSentTo($notificationSubscription->user, AlertOpenedNotification::class);
});
