<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\AlertCleared;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\ConditionObserved;
use Maarheeze\Compass\Alerts\Listeners\ObservationHandler;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;

test('handle opens an alert for a present condition without an alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $observedAt = CarbonImmutable::instance(fake()->dateTime());

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        AlertCondition::PRESENT,
        $observedAt,
    ));

    $this->assertDatabaseHas(Alert::class, [
        'subject_type' => $siteEndpoint->getMorphClass(),
        'subject_id' => $siteEndpoint->id,
        'type' => AlertType::DOWN_SITE_ENDPOINT,
        'condition' => AlertCondition::PRESENT,
        'last_observed_at' => $observedAt,
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
        AlertCondition::PRESENT,
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

test('handle opens an alert for a present condition when another subject has an alert of the type', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    Alert::factory()
        ->downSiteEndpoint()
        ->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        AlertCondition::PRESENT,
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
        AlertCondition::PRESENT,
        CarbonImmutable::now(),
    ));

    expect($siteEndpoint->alerts()->count())
        ->toBe(1);
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
        AlertCondition::PRESENT,
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
        AlertCondition::GONE,
        $observedAt,
    ));

    $this->assertDatabaseHas(Alert::class, [
        'id' => $storedAlert->id,
        'condition' => AlertCondition::GONE,
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
        AlertCondition::GONE,
        CarbonImmutable::now(),
    ));

    $eventFake->assertDispatched(AlertCleared::class, static function (AlertCleared $alertCleared) use ($storedAlert): bool {
        return $alertCleared->alert->is($storedAlert);
    });
});

test('handle raises nothing for a gone condition without an alert', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $siteEndpoint = SiteEndpoint::factory()->create();

    $observationHandler = $this->app->make(ObservationHandler::class);
    $observationHandler->handle(new ConditionObserved(
        AlertType::DOWN_SITE_ENDPOINT,
        $siteEndpoint,
        AlertCondition::GONE,
        CarbonImmutable::now(),
    ));

    $eventFake->assertNothingDispatched();
});
