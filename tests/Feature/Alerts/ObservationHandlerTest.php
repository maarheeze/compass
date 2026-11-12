<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Cache\LockProvider;
use Illuminate\Contracts\Cache\Repository as CacheRepository;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\ConnectionInterface;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\AlertRecurred;
use Maarheeze\Compass\Alerts\Events\AlertResolved;
use Maarheeze\Compass\Alerts\ObservationHandler;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\SiteEndpoint;
use Webmozart\Assert\Assert;

test('handle opens an alert for an active condition without an alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $this->assertDatabaseHas(Alert::class, [
        'subject_type' => $siteEndpoint->getMorphClass(),
        'subject_id' => $siteEndpoint->id,
        'alert_type_identifier' => new SiteEndpointDown()->identifier(),
        'condition' => ConditionState::ACTIVE,
        'status' => AlertStatus::OPEN,
        'is_muted' => false,
    ]);
});

test('handle refuses an observation of an alert type that is not registered', function (): void {
    $unregisteredAlertType = new readonly class implements AlertType {
        public function group(): AlertTypeGroup
        {
            return AlertTypeGroup::CUSTOM;
        }

        public function identifier(): string
        {
            return 'unregistered-alert-type';
        }

        public function translationKey(): string
        {
            return 'custom::alert-type.UNREGISTERED_ALERT_TYPE';
        }
    };

    $observationHandler = $this->app->make(ObservationHandler::class);

    $this->expectException(LogicException::class);

    $observationHandler->handle(new ConditionObservation(
        $unregisteredAlertType,
        SiteEndpoint::factory()->create(),
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));
});

test('handle raises alert opened with the opened alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->where('alert_type_identifier', new SiteEndpointDown()->identifier())
        ->sole();
    $eventFake->assertDispatched(AlertOpened::class, static function (AlertOpened $alertOpened) use ($openedAlert): bool {
        return $alertOpened->alert->is($openedAlert);
    });
});

test('handle writes an opened history entry for the opened alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::ACTIVE,
        $observedAt,
    ));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->where('alert_type_identifier', new SiteEndpointDown()->identifier())
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
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->done()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->done()
        ->for($siteEndpoint, 'subject')
        ->create([
            'condition' => ConditionState::ACTIVE,
            'resolved_at' => null,
        ]);

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    $eventFake->assertNothingDispatched();
});

test('handle resolves an active alert for a resolved condition', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->siteEndpointDown()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->active()
        ->for($siteEndpoint, 'subject')
        ->create();
    $connection = $this->app->make(ConnectionInterface::class);
    $connection->beginTransaction();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::RESOLVED,
        CarbonImmutable::now(),
    ));

    $eventFake->assertNothingDispatched();
});

test('handle recurs a resolved alert for an active condition', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $storedAlert = Alert::factory()
        ->siteEndpointDown()
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
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
        ->siteEndpointDown()
        ->resolved()
        ->for($siteEndpoint, 'subject')
        ->create();
    $connection = $this->app->make(ConnectionInterface::class);
    $connection->beginTransaction();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));
    $connection->rollBack();

    $eventFake->assertNotDispatched(AlertRecurred::class);
});

test('handle holds the lock of the alert type and subject while it changes the alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $lockKey = sprintf(
        '%s:%s:%s',
        new SiteEndpointDown()->identifier(),
        $siteEndpoint->getMorphClass(),
        $siteEndpoint->id->toString(),
    );
    $lockProvider = Assert::isInstanceOf($this->app->make(CacheRepository::class)->getStore(), LockProvider::class);
    $isLockFree = null;
    $this->app
        ->make(Dispatcher::class)
        ->listen(AlertOpened::class, static function () use ($lockKey, $lockProvider, &$isLockFree): void {
            $isLockFree = $lockProvider->lock($lockKey)->get();
        });

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    expect($isLockFree)
        ->toBeFalse();
});

test('handle leaves the lock of another subject free', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $otherSiteEndpoint = SiteEndpoint::factory()->create();
    $lockKey = sprintf(
        '%s:%s:%s',
        new SiteEndpointDown()->identifier(),
        $otherSiteEndpoint->getMorphClass(),
        $otherSiteEndpoint->id->toString(),
    );
    $lockProvider = Assert::isInstanceOf($this->app->make(CacheRepository::class)->getStore(), LockProvider::class);
    $isLockFree = null;
    $this->app
        ->make(Dispatcher::class)
        ->listen(AlertOpened::class, static function () use ($lockKey, $lockProvider, &$isLockFree): void {
            $isLockFree = $lockProvider->lock($lockKey)->get();
        });

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::ACTIVE,
        CarbonImmutable::now(),
    ));

    expect($isLockFree)
        ->toBeTrue();
});

test('handle stores a resolved condition without an alert as an alert check', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::RESOLVED,
        $observedAt,
    ));

    $this->assertDatabaseHas(AlertCheck::class, [
        'subject_type' => $siteEndpoint->getMorphClass(),
        'subject_id' => $siteEndpoint->id,
        'alert_type_identifier' => new SiteEndpointDown()->identifier(),
        'condition' => ConditionState::RESOLVED,
        'observed_at' => $observedAt,
    ]);
});

test('handle stores the observed condition on the existing alert check', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $alertCheck = AlertCheck::factory()
        ->for($siteEndpoint, 'subject')
        ->create([
            'alert_type_identifier' => new SiteEndpointDown()->identifier(),
            'condition' => ConditionState::ACTIVE,
        ]);
    $observedAt = CarbonImmutable::now();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        ConditionState::RESOLVED,
        $observedAt,
    ));

    $this->assertDatabaseHas(AlertCheck::class, [
        'id' => $alertCheck->id,
        'condition' => ConditionState::RESOLVED,
        'observed_at' => $observedAt,
    ]);
});

test('handle adds no second alert check for an alert type and subject with an alert check', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    AlertCheck::factory()
        ->for($siteEndpoint, 'subject')
        ->create(['alert_type_identifier' => new SiteEndpointDown()->identifier()]);

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObservation(
        new SiteEndpointDown(),
        $siteEndpoint,
        fake()->randomElement(ConditionState::cases()),
        CarbonImmutable::now(),
    ));

    expect($siteEndpoint->alertChecks()->count())
        ->toBe(1);
});
