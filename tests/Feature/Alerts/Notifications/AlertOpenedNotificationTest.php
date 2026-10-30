<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedMailable;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedNotification;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;

test('via is the channels the user subscribed to for the type of the alert', function (): void {
    $alert = Alert::factory()->create();
    $notificationSubscription = NotificationSubscription::factory()->create(['alert_type' => $alert->type]);

    $alertOpenedNotification = new AlertOpenedNotification($alert);
    $channels = $alertOpenedNotification->via($notificationSubscription->user);

    expect($channels)
        ->toBe([$notificationSubscription->channel->value]);
});

test('via leaves out the channels the user subscribed to for another alert type', function (): void {
    [$alertType, $otherAlertType] = fake()->randomElements(AlertType::cases(), 2);
    $alert = Alert::factory()->create(['type' => $alertType]);
    $notificationSubscription = NotificationSubscription::factory()->create(['alert_type' => $otherAlertType]);

    $alertOpenedNotification = new AlertOpenedNotification($alert);
    $channels = $alertOpenedNotification->via($notificationSubscription->user);

    expect($channels)
        ->toBeEmpty();
});

test('to mail is the mail of the alert to the email of the user', function (): void {
    $alert = Alert::factory()->create();
    $user = User::factory()->create();

    $alertOpenedNotification = new AlertOpenedNotification($alert);
    $alertOpenedMailable = $alertOpenedNotification->toMail($user);

    expect($alertOpenedMailable)
        ->toBeInstanceOf(AlertOpenedMailable::class)
        ->and($alertOpenedMailable->alert->is($alert))
        ->toBeTrue()
        ->and($alertOpenedMailable->hasTo($user->email))
        ->toBeTrue();
});
