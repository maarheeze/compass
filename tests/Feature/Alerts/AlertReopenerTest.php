<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertReopener;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\User;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('can reopen is true for a done alert', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertReopener = $this->app->make(AlertReopener::class);
    $canReopen = $alertReopener->canReopen($alert);

    expect($canReopen)
        ->toBeTrue();
});

test('can reopen is false for an alert that is not done', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();

    $alertReopener = $this->app->make(AlertReopener::class);
    $canReopen = $alertReopener->canReopen($alert);

    expect($canReopen)
        ->toBeFalse();
});

test('can reopen is false for a done alert whose subject has an alert of that type that is not done', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->debugEnabled()
        ->done()
        ->for($site, 'subject')
        ->create();
    Alert::factory()
        ->debugEnabled()
        ->active()
        ->for($site, 'subject')
        ->create();

    $alertReopener = $this->app->make(AlertReopener::class);
    $canReopen = $alertReopener->canReopen($alert);

    expect($canReopen)
        ->toBeFalse();
});

test('can reopen is true for a done alert whose subject has an alert of another type that is not done', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->debugEnabled()
        ->done()
        ->for($site, 'subject')
        ->create();
    Alert::factory()
        ->nonProduction()
        ->active()
        ->for($site, 'subject')
        ->create();

    $alertReopener = $this->app->make(AlertReopener::class);
    $canReopen = $alertReopener->canReopen($alert);

    expect($canReopen)
        ->toBeTrue();
});

test('can reopen is true for a done alert when another subject has an alert of that type that is not done', function (): void {
    $alert = Alert::factory()
        ->debugEnabled()
        ->done()
        ->create();
    Alert::factory()
        ->debugEnabled()
        ->active()
        ->create();

    $alertReopener = $this->app->make(AlertReopener::class);
    $canReopen = $alertReopener->canReopen($alert);

    expect($canReopen)
        ->toBeTrue();
});

test('reopen sets a done alert back to open', function (): void {
    $alert = Alert::factory()
        ->done()
        ->create();

    $alertReopener = $this->app->make(AlertReopener::class);
    $alertReopener->reopen($alert, User::factory()->create());

    $this->assertDatabaseHas(Alert::class, [
        'id' => $alert->id,
        'status' => AlertStatus::OPEN,
        'done_at' => null,
    ]);
});

test('reopen writes a reopened history entry with the user', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $alert = Alert::factory()
        ->done()
        ->create();
    $user = User::factory()->create();

    $alertReopener = $this->app->make(AlertReopener::class);
    $alertReopener->reopen($alert, $user);

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $alert->id,
        'type' => AlertHistoryEntryType::REOPENED,
        'occurred_at' => $now,
        'user_id' => $user->id,
    ]);
});

test('reopen refuses an alert that is not done', function (): void {
    $alert = Alert::factory()
        ->resolved()
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertReopener = $this->app->make(AlertReopener::class);
    $alertReopener->reopen($alert, User::factory()->create());
});

test('reopen refuses a done alert whose subject has an alert of that type that is not done', function (): void {
    $site = Site::factory()->create();
    $alert = Alert::factory()
        ->debugEnabled()
        ->done()
        ->for($site, 'subject')
        ->create();
    Alert::factory()
        ->debugEnabled()
        ->active()
        ->for($site, 'subject')
        ->create();

    $this->expectException(ModelNotFoundException::class);

    $alertReopener = $this->app->make(AlertReopener::class);
    $alertReopener->reopen($alert, User::factory()->create());
});
