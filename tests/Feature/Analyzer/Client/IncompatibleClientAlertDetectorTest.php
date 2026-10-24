<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Client\IncompatibleClientAlertDetector;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Models\Site;

test('find alerts finds a site whose client is incompatible', function (): void {
    $site = Site::factory()->create(['client_status' => ClientStatus::INCOMPATIBLE]);

    $incompatibleClientAlertDetector = $this->app->make(IncompatibleClientAlertDetector::class);
    $alerts = $incompatibleClientAlertDetector->findAlerts();

    expect($alerts->sole()->site->is($site))
        ->toBeTrue();
});

test('find alerts leaves out a site whose client is compatible', function (): void {
    Site::factory()->create(['client_status' => ClientStatus::INSTALLED]);

    $incompatibleClientAlertDetector = $this->app->make(IncompatibleClientAlertDetector::class);
    $alerts = $incompatibleClientAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
