<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Collections\AlertHistoryEntryCollection;
use Maarheeze\Compass\Collections\ObservationCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\Observation;
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

test('show contains the observations of the alert, newest first', function (): void {
    $alert = Alert::factory()->create();
    $earlierObservation = Observation::factory()->create([
        'alert_id' => $alert->id,
        'observed_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $laterObservation = Observation::factory()->create([
        'alert_id' => $alert->id,
        'observed_at' => fake()->dateTimeBetween('-1 month'),
    ]);
    Observation::factory()->create(['alert_id' => Alert::factory()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ALERT, $alert));

    $response->assertViewHas(
        'observations',
        static function (
            ObservationCollection $observations,
        ) use (
            $earlierObservation,
            $laterObservation,
        ): bool {
            return $observations->count() === 2
                && $observations->first()->id->equals($laterObservation->id)
                && $observations->last()->id->equals($earlierObservation->id);
        },
    );
});

test('mark done marks the alert done by the signed in user', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_MARK_DONE, $alert));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::MARKED_DONE,
        'user_id' => $user->id,
    ]);
});

test('mark done sends the user back to the dashboard', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_MARK_DONE, $alert));

    $response->assertRedirect(route(RouteName::DASHBOARD));
});

test('reopen reopens the alert by the signed in user', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_REOPEN, $alert));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::REOPENED,
        'user_id' => $user->id,
    ]);
});

test('reopen sends the user back to the alert page', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_REOPEN, $alert));

    $response->assertRedirect(route(RouteName::ALERT, $alert));
});

test('mute mutes the alert by the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_MUTE, $alert));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::MUTED,
        'user_id' => $user->id,
    ]);
});

test('mute sends the user back to the alert page', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_MUTE, $alert));

    $response->assertRedirect(route(RouteName::ALERT, $alert));
});

test('unmute unmutes the alert by the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_UNMUTE, $alert));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::UNMUTED,
        'user_id' => $user->id,
    ]);
});

test('unmute sends the user back to the alert page', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_UNMUTE, $alert));

    $response->assertRedirect(route(RouteName::ALERT, $alert));
});

test('assign assigns the alert to the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_ASSIGN, $alert));

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'assignee_id' => $user->id,
    ]);
});

test('assign sends the user back to the alert page', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_ASSIGN, $alert));

    $response->assertRedirect(route(RouteName::ALERT, $alert));
});

test('unassign unassigns the alert by the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();
    $user = User::factory()->create();
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_UNASSIGN, $alert));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::UNASSIGNED,
        'user_id' => $user->id,
    ]);
});

test('unassign sends the user back to the alert page', function (): void {
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_UNASSIGN, $alert));

    $response->assertRedirect(route(RouteName::ALERT, $alert));
});
