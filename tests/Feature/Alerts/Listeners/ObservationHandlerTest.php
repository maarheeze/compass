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
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\AlertRecurred;
use Maarheeze\Compass\Alerts\Events\AlertResolved;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\Observation;
use Maarheeze\Compass\Models\SiteEndpoint;

test('handle opens an alert for an active condition without an alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $this->assertDatabaseHas(Alert::class, [
        'subject_type' => $siteEndpoint->getMorphClass(),
        'subject_id' => $siteEndpoint->id,
        'type' => AlertType::DOWN_SITE_ENDPOINT,
        'condition' => ConditionState::ACTIVE,
        'status' => AlertStatus::OPEN,
        'is_muted' => false,
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
        ConditionState::ACTIVE,
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
        ConditionState::ACTIVE,
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
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));
    $connection->rollBack();

    $eventFake->assertNotDispatched(AlertOpened::class);
});

test('handle opens an alert for an active condition when another subject has an alert of the type', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    expect($siteEndpoint->alerts()->count())
        ->toBe(1);
});

test('handle opens no second alert for an active condition with an active alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    expect($siteEndpoint->alerts()->count())
        ->toBe(1);
});

test('handle opens a new alert for an active condition with only a done alert', function (): void {
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
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->notDone()
        ->sole();
    expect($openedAlert->is($doneAlert))
        ->toBeFalse();
});

test('handle opens a new alert for an active condition with an alert marked done while active', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $doneAlert = Alert::factory()
        ->downSiteEndpoint()
        ->done()
        ->for($siteEndpoint, 'subject')
        ->create([
            'condition' => ConditionState::ACTIVE,
            'resolved_at' => null,
        ]);

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->notDone()
        ->sole();
    expect($openedAlert->is($doneAlert))
        ->toBeFalse();
});

test('handle raises nothing for an active condition with an active alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $eventFake->assertNothingDispatched();
});

test('handle resolves an active alert for a resolved condition', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::RESOLVED,
        $observedAt,
    ));

    $this->assertDatabaseHas(Alert::class, [
        'id' => $storedAlert->id,
        'condition' => ConditionState::RESOLVED,
        'resolved_at' => $observedAt,
    ]);
});

test('handle raises alert resolved with the resolved alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::RESOLVED,
        CarbonImmutable::now(),
    ));

    $eventFake->assertDispatched(AlertResolved::class, static function (AlertResolved $alertResolved) use ($storedAlert): bool {
        return $alertResolved->alert->is($storedAlert);
    });
});

test('handle writes a resolved history entry for the resolved alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::RESOLVED,
        $observedAt,
    ));

    $this->assertDatabaseHas(AlertHistoryEntry::class, [
        'alert_id' => $storedAlert->id,
        'type' => AlertHistoryEntryType::RESOLVED,
        'occurred_at' => $observedAt,
    ]);
});

test('handle raises no alert resolved when its change is rolled back', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();
    $connection = $this->app->make(ConnectionInterface::class);
    $connection->beginTransaction();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::RESOLVED,
        CarbonImmutable::now(),
    ));
    $connection->rollBack();

    $eventFake->assertNotDispatched(AlertResolved::class);
});

test('handle raises nothing for a resolved condition without an alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::RESOLVED,
        CarbonImmutable::now(),
    ));

    $eventFake->assertNothingDispatched();
});

test('handle recurs a resolved alert for an active condition', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $this->assertDatabaseHas(Alert::class, [
        'id' => $storedAlert->id,
        'condition' => ConditionState::ACTIVE,
        'resolved_at' => null,
    ]);
});

test('handle raises alert recurred with the recurred alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->downSiteEndpoint()
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
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
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
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
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();
    $connection = $this->app->make(ConnectionInterface::class);
    $connection->beginTransaction();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
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

test('handle stores an observation without an alert unattached', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $observedAt = CarbonImmutable::parse(fake()->iso8601());

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::RESOLVED,
        $observedAt,
    ));

    $this->assertDatabaseHas(Observation::class, [
        'alert_id' => null,
        'subject_type' => $siteEndpoint->getMorphClass(),
        'subject_id' => $siteEndpoint->id,
        'alert_type' => AlertType::DOWN_SITE_ENDPOINT,
        'condition' => ConditionState::RESOLVED,
        'observed_at' => $observedAt,
    ]);
});

test('handle attaches an active observation to the active alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $activeAlert = Alert::factory()
        ->downSiteEndpoint()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::parse(fake()->iso8601());

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        $observedAt,
    ));

    $this->assertDatabaseHas(Observation::class, [
        'alert_id' => $activeAlert->id,
        'condition' => ConditionState::ACTIVE,
        'observed_at' => $observedAt,
    ]);
});

test('handle attaches the opening observation to the opened alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $observedAt = CarbonImmutable::parse(fake()->iso8601());

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        ConditionState::ACTIVE,
        $observedAt,
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->where('type', AlertType::DOWN_SITE_ENDPOINT)
        ->sole();
    $this->assertDatabaseHas(Observation::class, [
        'alert_id' => $openedAlert->id,
        'condition' => ConditionState::ACTIVE,
        'observed_at' => $observedAt,
    ]);
});
