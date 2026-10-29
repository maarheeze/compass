<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maarheeze\Compass\Alerts\AlertAssigner;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\User;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('can assign to me is true for an alert without an assignee', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $canAssignToMe = $alertAssigner->canAssignToMe($alert, User::factory()->create());

    expect($canAssignToMe)
        ->toBeTrue();
});

test('can assign to me is true for an alert assigned to someone else', function (): void {
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $canAssignToMe = $alertAssigner->canAssignToMe($alert, User::factory()->create());

    expect($canAssignToMe)
        ->toBeTrue();
});

test('can assign to me is false for a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $canAssignToMe = $alertAssigner->canAssignToMe($alert, User::factory()->create());

    expect($canAssignToMe)
        ->toBeFalse();
});

test('can assign to me is false for an alert assigned to the user', function (): void {
    $user = User::factory()->create();
    $alert = Alert::factory()
        ->active()
        ->for($user, 'assignee')
        ->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $canAssignToMe = $alertAssigner->canAssignToMe($alert, $user);

    expect($canAssignToMe)
        ->toBeFalse();
});

test('can unassign is true for an alert with an assignee', function (): void {
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $canUnassign = $alertAssigner->canUnassign($alert);

    expect($canUnassign)
        ->toBeTrue();
});

test('can unassign is false for an alert without an assignee', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $canUnassign = $alertAssigner->canUnassign($alert);

    expect($canUnassign)
        ->toBeFalse();
});

test('assign to me makes the user the assignee', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();
    $user = User::factory()->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $alertAssigner->assignToMe($alert, $user);

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'assignee_id' => $user->id,
    ]);
});

test('assign to me writes an assigned history entry with the user', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->active()
        ->create();
    $user = User::factory()->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $alertAssigner->assignToMe($alert, $user);

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::ASSIGNED,
        'occurred_at' => $now,
        'user_id' => $user->id,
    ]);
});

test('assign to me refuses a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $alertAssigner->assignToMe($alert, User::factory()->create());
});

test('assign to me refuses an alert assigned to the user', function (): void {
    $user = User::factory()->create();
    $alert = Alert::factory()
        ->active()
        ->for($user, 'assignee')
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $alertAssigner->assignToMe($alert, $user);
});

test('unassign clears the assignee', function (): void {
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $alertAssigner->unassign($alert, User::factory()->create());

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'assignee_id' => null,
    ]);
});

test('unassign writes an unassigned history entry with the user', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->active()
        ->for(User::factory(), 'assignee')
        ->create();
    $user = User::factory()->create();

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $alertAssigner->unassign($alert, $user);

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::UNASSIGNED,
        'occurred_at' => $now,
        'user_id' => $user->id,
    ]);
});

test('unassign refuses an alert without an assignee', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['assignee_id' => null]);

    $this->expectException(ModelNotFoundException::class);

    $alertAssigner = $this->app->make(AlertAssigner::class);
    $alertAssigner->unassign($alert, User::factory()->create());
});
