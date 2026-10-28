<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\AlertCleared;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\AlertRecurred;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\SiteEndpoint;

test('handle opens an alert for a present condition without an alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    $this->assertDatabaseHas(Alert::class, [
        'subject_type' => $siteEndpoint->getMorphClass(),
        'subject_id' => $siteEndpoint->id,
        'type' => AlertType::DOWN_SITE_ENDPOINT,
        'condition' => ConditionState::PRESENT,
        'status' => AlertStatus::OPEN,
    ]);
});

test('handle raises alert opened with the opened alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->where('type', AlertType::DOWN_SITE_ENDPOINT)
        ->sole();
    $eventFake->assertDispatched(AlertOpened::class, static function (AlertOpened $alertOpened) use ($openedAlert): bool {
        return $alertOpened->alert->is($openedAlert);
    });
});

test('handle writes an opened history entry for the opened alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        $observedAt,
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->where('type', AlertType::DOWN_SITE_ENDPOINT)
        ->sole();
    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $openedAlert->id,
        'type' => AlertHistoryEntryType::OPENED,
        'occurred_at' => $observedAt,
    ]);
});

test('handle raises no alert opened when its change is rolled back', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $connection = $this->app->make(ConnectionInterface::class);
    $connection->beginTransaction();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));
    $connection->rollBack();

    $eventFake->assertNotDispatched(AlertOpened::class);
});

test('handle opens an alert for a present condition when another subject has an alert of the type', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    expect($siteEndpoint->alerts()->count())
        ->toBe(1);
});

test('handle opens no second alert for a present condition with a present alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->present()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    expect($siteEndpoint->alerts()->count())
        ->toBe(1);
});

test('handle opens a new alert for a present condition with only a done alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $doneAlert = Alert::factory()
        ->downSiteEndpoint()
        ->done()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->notDone()
        ->sole();
    expect($openedAlert->is($doneAlert))
        ->toBeFalse();
});

test('handle raises nothing for a present condition with a present alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->present()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    $eventFake->assertNothingDispatched();
});

test('handle clears a present alert for a gone condition', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->present()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::GONE,
        $observedAt,
    ));

    $this->assertDatabaseHas(Alert::class, [
        'id' => $storedAlert->id,
        'condition' => ConditionState::GONE,
        'gone_at' => $observedAt,
    ]);
});

test('handle raises alert cleared with the cleared alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->present()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::GONE,
        CarbonImmutable::now(),
    ));

    $eventFake->assertDispatched(AlertCleared::class, static function (AlertCleared $alertCleared) use ($storedAlert): bool {
        return $alertCleared->alert->is($storedAlert);
    });
});

test('handle writes a cleared history entry for the cleared alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->present()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::GONE,
        $observedAt,
    ));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $storedAlert->id,
        'type' => AlertHistoryEntryType::CLEARED,
        'occurred_at' => $observedAt,
    ]);
});

test('handle raises no alert cleared when its change is rolled back', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->present()
        ->for($siteEndpoint, 'subject')
        ->create();
    $connection = $this->app->make(ConnectionInterface::class);
    $connection->beginTransaction();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::GONE,
        CarbonImmutable::now(),
    ));
    $connection->rollBack();

    $eventFake->assertNotDispatched(AlertCleared::class);
});

test('handle raises nothing for a gone condition without an alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::GONE,
        CarbonImmutable::now(),
    ));

    $eventFake->assertNothingDispatched();
});

test('handle recurs a gone alert for a present condition', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->gone()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    $this->assertDatabaseHas(Alert::class, [
        'id' => $storedAlert->id,
        'condition' => ConditionState::PRESENT,
        'gone_at' => null,
    ]);
});

test('handle raises alert recurred with the recurred alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->gone()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));

    $eventFake->assertDispatched(AlertRecurred::class, static function (AlertRecurred $alertRecurred) use ($storedAlert): bool {
        return $alertRecurred->alert->is($storedAlert);
    });
});

test('handle writes a recurred history entry for the recurred alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->gone()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        $observedAt,
    ));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $storedAlert->id,
        'type' => AlertHistoryEntryType::RECURRED,
        'occurred_at' => $observedAt,
    ]);
});

test('handle raises no alert recurred when its change is rolled back', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->gone()
        ->for($siteEndpoint, 'subject')
        ->create();
    $connection = $this->app->make(ConnectionInterface::class);
    $connection->beginTransaction();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::PRESENT,
        CarbonImmutable::now(),
    ));
    $connection->rollBack();

    $eventFake->assertNotDispatched(AlertRecurred::class);
});

test('middleware locks observations of one alert under the same key', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $firstMiddleware = $observationHandler->middleware(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        fake()->randomElement(ConditionState::cases()),
        CarbonImmutable::now(),
    ));
    $secondMiddleware = $observationHandler->middleware(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        fake()->randomElement(ConditionState::cases()),
        CarbonImmutable::now(),
    ));

    expect($secondMiddleware[0]->key)
        ->toBe($firstMiddleware[0]->key);
});

test('middleware locks observations of different subjects under different keys', function (): void {
    $observationHandler = $this->app->make(ObservationHandler::class);
    $firstMiddleware = $observationHandler->middleware(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        SiteEndpoint::factory()->create(),
        fake()->randomElement(ConditionState::cases()),
        CarbonImmutable::now(),
    ));
    $secondMiddleware = $observationHandler->middleware(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        SiteEndpoint::factory()->create(),
        fake()->randomElement(ConditionState::cases()),
        CarbonImmutable::now(),
    ));

    expect($secondMiddleware[0]->key)
        ->not->toBe($firstMiddleware[0]->key);
});
