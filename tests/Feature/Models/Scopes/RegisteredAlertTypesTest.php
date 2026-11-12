<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\NotificationSubscription;

test('apply keeps an alert of a registered alert type', function (): void {
    $alert = Alert::factory()->create();

    $alerts = Alert::query()->get();

    expect($alerts->sole()->is($alert))
        ->toBeTrue();
});

test('apply leaves out an alert of an alert type that is not registered', function (): void {
    Alert::factory()->create(['alert_type_identifier' => fake()->slug()]);

    $alerts = Alert::query()->get();

    expect($alerts)
        ->toBeEmpty();
});

test('apply leaves out an alert check of an alert type that is not registered', function (): void {
    AlertCheck::factory()->create(['alert_type_identifier' => fake()->slug()]);

    $alertChecks = AlertCheck::query()->get();

    expect($alertChecks)
        ->toBeEmpty();
});

test('apply leaves out a notification subscription to an alert type that is not registered', function (): void {
    NotificationSubscription::factory()->create(['alert_type_identifier' => fake()->slug()]);

    $notificationSubscriptions = NotificationSubscription::query()->get();

    expect($notificationSubscriptions)
        ->toBeEmpty();
});
