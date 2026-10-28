<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\Events\AlertOpened;
use Maarheeze\Compass\Alerts\Events\ConditionPresent;
use Maarheeze\Compass\Alerts\Listeners\AlertRecorder;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;

test('handle opens an alert for a present condition without an alert', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    $observedAt = CarbonImmutable::instance(fake()->dateTime());

    $alertRecorder = $this->app->make(AlertRecorder::class);
    $alertRecorder->handle(new ConditionPresent(AlertType::DOWN_SITE_ENDPOINT, $siteEndpoint, $observedAt));

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

    $alertRecorder = $this->app->make(AlertRecorder::class);
    $alertRecorder->handle(new ConditionPresent(AlertType::DOWN_SITE_ENDPOINT, $siteEndpoint, CarbonImmutable::now()));

    $openedAlert = Alert::query()
        ->whereMorphedTo('subject', $siteEndpoint)
        ->where('type', AlertType::DOWN_SITE_ENDPOINT)
        ->sole();
    $eventFake->assertDispatched(AlertOpened::class, static function (AlertOpened $alertOpened) use ($openedAlert): bool {
        return $alertOpened->alert->is($openedAlert);
    });
});
