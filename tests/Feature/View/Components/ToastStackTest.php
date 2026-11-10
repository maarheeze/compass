<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\View\Components\ToastStack;

test('confirmed entry type is the history entry type of the alert confirmation', function (): void {
    $alertHistoryEntryType = fake()->randomElement(AlertHistoryEntryType::cases());
    $this->app
        ->make(AlertConfirmation::class)
        ->flash($alertHistoryEntryType);

    $toastStack = $this->app->make(ToastStack::class);

    expect($toastStack->confirmedEntryType)
        ->toBe($alertHistoryEntryType);
});
