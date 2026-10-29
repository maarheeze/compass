<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertMuter;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\User;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('can mute is true for an alert that is not done and not muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $canMute = $alertMuter->canMute($alert);

    expect($canMute)
        ->toBeTrue();
});

test('can mute is true for an alert whose mute has passed', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create(['muted_until' => fake()->dateTimeBetween('-1 month')]);

    $alertMuter = $this->app->make(AlertMuter::class);
    $canMute = $alertMuter->canMute($alert);

    expect($canMute)
        ->toBeTrue();
});

test('can mute is false for a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $canMute = $alertMuter->canMute($alert);

    expect($canMute)
        ->toBeFalse();
});

test('can mute is false for a muted alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $canMute = $alertMuter->canMute($alert);

    expect($canMute)
        ->toBeFalse();
});

test('can unmute is true for a muted alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $canUnmute = $alertMuter->canUnmute($alert);

    expect($canUnmute)
        ->toBeTrue();
});

test('can unmute is false for an alert that is not muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $canUnmute = $alertMuter->canUnmute($alert);

    expect($canUnmute)
        ->toBeFalse();
});

test('mute mutes the alert for one hour from now', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->active()
        ->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $alertMuter->mute($alert, User::factory()->create());

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'muted_until' => $now->addHour(),
    ]);
});

test('mute writes a muted history entry with the user', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->active()
        ->create();
    $user = User::factory()->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $alertMuter->mute($alert, $user);

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::MUTED,
        'occurred_at' => $now,
        'user_id' => $user->id,
    ]);
});

test('mute refuses a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertMuter = $this->app->make(AlertMuter::class);
    $alertMuter->mute($alert, User::factory()->create());
});

test('mute refuses a muted alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertMuter = $this->app->make(AlertMuter::class);
    $alertMuter->mute($alert, User::factory()->create());
});

test('unmute empties the mute of the alert', function (): void {
    $alert = Alert::factory()
        ->active()
        ->muted()
        ->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $alertMuter->unmute($alert, User::factory()->create());

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'muted_until' => null,
    ]);
});

test('unmute writes an unmuted history entry with the user', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->active()
        ->create(['muted_until' => $now->addMinutes(fake()->numberBetween(1, 60))]);
    $user = User::factory()->create();

    $alertMuter = $this->app->make(AlertMuter::class);
    $alertMuter->unmute($alert, $user);

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::UNMUTED,
        'occurred_at' => $now,
        'user_id' => $user->id,
    ]);
});

test('unmute refuses an alert that is not muted', function (): void {
    $alert = Alert::factory()
        ->active()
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertMuter = $this->app->make(AlertMuter::class);
    $alertMuter->unmute($alert, User::factory()->create());
});
