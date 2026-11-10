<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\View\Components\AlertToast;
use Maarheeze\Compass\View\ToastType;

test('should render is true for an entry compass records', function (): void {
    $alertHistoryEntry = AlertHistoryEntry::factory()->create([
        'type' => fake()->randomElement([
            AlertHistoryEntryType::OPENED,
            AlertHistoryEntryType::RECURRED,
            AlertHistoryEntryType::RESOLVED,
        ]),
    ]);

    $alertToast = $this->app->make(AlertToast::class, ['alertHistoryEntry' => $alertHistoryEntry]);
    $shouldRender = $alertToast->shouldRender();

    expect($shouldRender)
        ->toBeTrue();
});

test('should render is false for an entry a person records', function (): void {
    $alertHistoryEntry = AlertHistoryEntry::factory()->create([
        'type' => fake()->randomElement([
            AlertHistoryEntryType::ASSIGNED,
            AlertHistoryEntryType::MARKED_DONE,
            AlertHistoryEntryType::MUTED,
            AlertHistoryEntryType::REOPENED,
            AlertHistoryEntryType::UNASSIGNED,
            AlertHistoryEntryType::UNMUTED,
        ]),
    ]);

    $alertToast = $this->app->make(AlertToast::class, ['alertHistoryEntry' => $alertHistoryEntry]);
    $shouldRender = $alertToast->shouldRender();

    expect($shouldRender)
        ->toBeFalse();
});

test('toast type of an opened or recurred entry is danger', function (): void {
    $alertHistoryEntry = AlertHistoryEntry::factory()->create([
        'type' => fake()->randomElement([AlertHistoryEntryType::OPENED, AlertHistoryEntryType::RECURRED]),
    ]);

    $alertToast = $this->app->make(AlertToast::class, ['alertHistoryEntry' => $alertHistoryEntry]);

    expect($alertToast->toastType())
        ->toBe(ToastType::DANGER);
});

test('toast type of a resolved entry is success', function (): void {
    $alertHistoryEntry = AlertHistoryEntry::factory()
        ->resolved()
        ->create();

    $alertToast = $this->app->make(AlertToast::class, ['alertHistoryEntry' => $alertHistoryEntry]);

    expect($alertToast->toastType())
        ->toBe(ToastType::SUCCESS);
});
