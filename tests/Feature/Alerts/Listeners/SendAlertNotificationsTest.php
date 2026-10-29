<?php

declare(strict_types=1);

use Illuminate\Contracts\Notifications\Dispatcher;
use Illuminate\Support\Testing\Fakes\NotificationFake;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Listeners\SendAlertNotifications;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedNotification;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\User;

test('handle notifies every user of the opened alert', function (): void {
    $notificationFake = new NotificationFake();
    $this->app->instance(Dispatcher::class, $notificationFake);
    $alert = Alert::factory()->create();
    $users = User::factory()
        ->count(2)
        ->create();

    $sendAlertNotifications = $this->app->make(SendAlertNotifications::class);
    $sendAlertNotifications->handle(new AlertOpened($alert));

    $notificationFake->assertSentTo(
        $users,
        AlertOpenedNotification::class,
        static function (AlertOpenedNotification $alertOpenedNotification) use ($alert): bool {
            return $alertOpenedNotification->alert->is($alert);
        },
    );
});
