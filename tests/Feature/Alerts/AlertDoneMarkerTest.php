<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maarheeze\Compass\Alerts\AlertDoneMarker;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\User;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('can mark done is true for a resolved alert', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();

    $alertDoneMarker = $this->app->make(AlertDoneMarker::class);
    $canMarkDone = $alertDoneMarker->canMarkDone($alert);

    expect($canMarkDone)
        ->toBeTrue();
});

test('can mark done is true for an active alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertDoneMarker = $this->app->make(AlertDoneMarker::class);
    $canMarkDone = $alertDoneMarker->canMarkDone($alert);

    expect($canMarkDone)
        ->toBeTrue();
});

test('can mark done is false for a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertDoneMarker = $this->app->make(AlertDoneMarker::class);
    $canMarkDone = $alertDoneMarker->canMarkDone($alert);

    expect($canMarkDone)
        ->toBeFalse();
});

test('mark done marks a resolved alert done now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->resolved()
        ->create();

    $alertDoneMarker = $this->app->make(AlertDoneMarker::class);
    $alertDoneMarker->markDone($alert, User::factory()->create());

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'status' => AlertStatus::DONE,
        'done_at' => $now,
    ]);
});

test('mark done writes a marked done history entry with the user', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->resolved()
        ->create();
    $user = User::factory()->create();

    $alertDoneMarker = $this->app->make(AlertDoneMarker::class);
    $alertDoneMarker->markDone($alert, $user);

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::MARKED_DONE,
        'occurred_at' => $now,
        'user_id' => $user->id,
    ]);
});

test('mark done marks an active alert done', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertDoneMarker = $this->app->make(AlertDoneMarker::class);
    $alertDoneMarker->markDone($alert, User::factory()->create());

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'status' => AlertStatus::DONE,
    ]);
});

test('mark done refuses a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertDoneMarker = $this->app->make(AlertDoneMarker::class);
    $alertDoneMarker->markDone($alert, User::factory()->create());
});
