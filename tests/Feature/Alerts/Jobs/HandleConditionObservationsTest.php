<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionObservation;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Jobs\HandleConditionObservations;
use Maarheeze\Compass\Collections\ConditionObservationCollection;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\SiteEndpoint;

test('handle handles each of its condition observations', function (): void {
    $firstSiteEndpoint = SiteEndpoint::factory()->create();
    $secondSiteEndpoint = SiteEndpoint::factory()->create();

    $handleConditionObservations = new HandleConditionObservations(new ConditionObservationCollection([
        new ConditionObservation(
            AlertType::SITE_ENDPOINT_DOWN,
            $firstSiteEndpoint,
            ConditionState::ACTIVE,
            CarbonImmutable::now(),
        ),
        new ConditionObservation(
            AlertType::SITE_ENDPOINT_DOWN,
            $secondSiteEndpoint,
            ConditionState::ACTIVE,
            CarbonImmutable::now(),
        ),
    ]));
    $this->app->call([$handleConditionObservations, 'handle']);

    $this->assertDatabaseHas(Alert::class, [
        'subject_type' => $firstSiteEndpoint->getMorphClass(),
        'subject_id' => $firstSiteEndpoint->id,
        'type' => AlertType::SITE_ENDPOINT_DOWN,
    ]);
    $this->assertDatabaseHas(Alert::class, [
        'subject_type' => $secondSiteEndpoint->getMorphClass(),
        'subject_id' => $secondSiteEndpoint->id,
        'type' => AlertType::SITE_ENDPOINT_DOWN,
    ]);
});
