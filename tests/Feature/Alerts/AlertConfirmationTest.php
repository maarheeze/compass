<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;

test('confirmed entry type is the flashed history entry type', function (): void {
    $alertHistoryEntryType = fake()->randomElement(AlertHistoryEntryType::cases());

    $alertConfirmation = $this->app->make(AlertConfirmation::class);
    $alertConfirmation->flash($alertHistoryEntryType);

    expect($alertConfirmation->confirmedEntryType())
        ->toBe($alertHistoryEntryType);
});

test('confirmed entry type is null when nothing was flashed', function (): void {
    $alertConfirmation = $this->app->make(AlertConfirmation::class);

    expect($alertConfirmation->confirmedEntryType())
        ->toBeNull();
});
