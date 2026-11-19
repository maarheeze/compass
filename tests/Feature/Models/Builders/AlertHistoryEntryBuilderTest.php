<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\AlertHistoryEntry;

test('listed is the entries newest first', function (): void {
    $earlierAlertHistoryEntry = AlertHistoryEntry::factory()->create([
        'occurred_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $laterAlertHistoryEntry = AlertHistoryEntry::factory()->create([
        'occurred_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $listedAlertHistoryEntries = AlertHistoryEntry::query()->listed();

    expect($listedAlertHistoryEntries->modelKeys())
        ->toEqual([$laterAlertHistoryEntry->getKey(), $earlierAlertHistoryEntry->getKey()]);
});

test('listed loads the user of the entries', function (): void {
    AlertHistoryEntry::factory()
        ->markedDone()
        ->create();

    $listedAlertHistoryEntries = AlertHistoryEntry::query()->listed();

    expect($listedAlertHistoryEntries->sole()->relationLoaded('user'))
        ->toBeTrue();
});
