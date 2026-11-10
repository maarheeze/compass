<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertConfirmation;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
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

test('show loads the user of the history entries', function (): void {
    $alert = Alert::factory()->create();
    AlertHistoryEntry::factory()
        ->markedDone()
        ->create(['alert_id' => $alert->id]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ALERT, $alert));

    $response->assertViewHas('historyEntries', static function (AlertHistoryEntryCollection $historyEntries): bool {
        return $historyEntries->sole()->relationLoaded('user');
    });
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

test('mark done confirms the marking done to the signed in user', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_MARK_DONE, $alert));

    expect($this->app->make(AlertConfirmation::class)->confirmedEntryType())
        ->toBe(AlertHistoryEntryType::MARKED_DONE);
});

test('mark done refuses an alert that is already done', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_MARK_DONE, $alert));

    $response->assertSessionHasErrors('alert');
});

test('mark done sends the user back to the alert page when the alert is already done', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_MARK_DONE, $alert));

    $response->assertRedirect(route(RouteName::ALERT, $alert));
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

test('reopen confirms the reopening to the signed in user', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_REOPEN, $alert));

    expect($this->app->make(AlertConfirmation::class)->confirmedEntryType())
        ->toBe(AlertHistoryEntryType::REOPENED);
});

test('reopen refuses an alert that is not done', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_REOPEN, $alert));

    $response->assertSessionHasErrors('alert');
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

test('mute confirms the muting to the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_MUTE, $alert));

    expect($this->app->make(AlertConfirmation::class)->confirmedEntryType())
        ->toBe(AlertHistoryEntryType::MUTED);
});

test('mute refuses an alert that is already muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_MUTE, $alert));

    $response->assertSessionHasErrors('alert');
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

test('unmute confirms the unmuting to the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_UNMUTE, $alert));

    expect($this->app->make(AlertConfirmation::class)->confirmedEntryType())
        ->toBe(AlertHistoryEntryType::UNMUTED);
});

test('unmute refuses an alert that is not muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_UNMUTE, $alert));

    $response->assertSessionHasErrors('alert');
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

test('assign confirms the assigning to the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_ASSIGN, $alert));

    expect($this->app->make(AlertConfirmation::class)->confirmedEntryType())
        ->toBe(AlertHistoryEntryType::ASSIGNED);
});

test('assign refuses an alert that is done', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_ASSIGN, $alert));

    $response->assertSessionHasErrors('alert');
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

test('unassign confirms the unassigning to the signed in user', function (): void {
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::ALERT_UNASSIGN, $alert));

    expect($this->app->make(AlertConfirmation::class)->confirmedEntryType())
        ->toBe(AlertHistoryEntryType::UNASSIGNED);
});

test('unassign refuses an alert that is not assigned', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::ALERT_UNASSIGN, $alert));

    $response->assertSessionHasErrors('alert');
});
