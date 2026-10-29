<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Notifications\AlertOpenedMailable;
use Maarheeze\Compass\Alerts\Notifications\AlertOpenedNotification;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\User;

test('via is mail', function (): void {
    $alertOpenedNotification = new AlertOpenedNotification(Alert::factory()->create());

    expect($alertOpenedNotification->via())
        ->toBe(['mail']);
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
