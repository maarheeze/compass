<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\Events\AlertChanged;
use Maarheeze\Compass\Alerts\Events\AlertHistoryEntryCreated;
use Maarheeze\Compass\Alerts\Listeners\BroadcastAlertHistoryEntry;
use Maarheeze\Compass\Models\AlertHistoryEntry;

test('handle broadcasts the toast of an entry compass records', function (): void {
    $alertHistoryEntry = AlertHistoryEntry::factory()
        ->opened()
        ->create();
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);

    $broadcastAlertHistoryEntry = $this->app->make(BroadcastAlertHistoryEntry::class);
    $broadcastAlertHistoryEntry->handle(new AlertHistoryEntryCreated($alertHistoryEntry));

    $eventFake->assertDispatched(AlertChanged::class, static function (AlertChanged $alertChanged): bool {
        return $alertChanged->toastHtml !== null;
    });
});

test('handle broadcasts no toast for an entry a person records', function (): void {
    $alertHistoryEntry = AlertHistoryEntry::factory()
        ->muted()
        ->create();
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);

    $broadcastAlertHistoryEntry = $this->app->make(BroadcastAlertHistoryEntry::class);
    $broadcastAlertHistoryEntry->handle(new AlertHistoryEntryCreated($alertHistoryEntry));

    $eventFake->assertDispatched(AlertChanged::class, static function (AlertChanged $alertChanged): bool {
        return $alertChanged->toastHtml === null;
    });
});
