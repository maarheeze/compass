<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;

test('not done keeps an open alert', function (): void {
    $openAlert = Alert::factory()
        ->present()
        ->create();

    $notDoneAlerts = Alert::query()
        ->notDone()
        ->get();

    expect($notDoneAlerts->sole()->is($openAlert))
        ->toBeTrue();
});

test('not done leaves out a done alert', function (): void {
    Alert::factory()
        ->done()
        ->create();

    $notDoneAlerts = Alert::query()
        ->notDone()
        ->get();

    expect($notDoneAlerts)
        ->toBeEmpty();
});
