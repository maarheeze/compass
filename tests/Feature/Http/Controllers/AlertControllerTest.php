<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\AlertHistoryEntryCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the alert', function (): void {
    $alert = Alert::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ALERT, $alert));

    $response->assertViewHas('alert', static function (Alert $viewAlert) use ($alert): bool {
        return $viewAlert->is($alert);
    });
});

test('show contains the history entries of the alert, newest first', function (): void {
    $alert = Alert::factory()->create();
    $earlierAlertHistoryEntry = AlertHistoryEntry::factory()->create([
        'alert_id' => $alert->id,
        'occurred_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $laterAlertHistoryEntry = AlertHistoryEntry::factory()->create([
        'alert_id' => $alert->id,
        'occurred_at' => fake()->dateTimeBetween('-1 month'),
    ]);
    AlertHistoryEntry::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ALERT, $alert));

    $response->assertViewHas(
        'historyEntries',
        static function (
            AlertHistoryEntryCollection $historyEntries,
        ) use (
            $earlierAlertHistoryEntry,
            $laterAlertHistoryEntry,
        ): bool {
            return $historyEntries->count() === 2
                && $historyEntries->first()->id->equals($laterAlertHistoryEntry->id)
                && $historyEntries->last()->id->equals($earlierAlertHistoryEntry->id);
        },
    );
});
