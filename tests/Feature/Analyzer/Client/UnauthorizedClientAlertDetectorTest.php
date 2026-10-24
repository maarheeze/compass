<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Client\UnauthorizedClientAlertDetector;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Models\Site;

test('find alerts finds a site whose client refuses the key', function (): void {
    $site = Site::factory()->create(['client_status' => ClientStatus::UNAUTHORIZED]);

    $unauthorizedClientAlertDetector = $this->app->make(UnauthorizedClientAlertDetector::class);
    $alerts = $unauthorizedClientAlertDetector->findAlerts();

    expect($alerts->sole()->site->is($site))
        ->toBeTrue();
});

test('find alerts leaves out a site whose client accepts the key', function (): void {
    Site::factory()->create(['client_status' => ClientStatus::INSTALLED]);

    $unauthorizedClientAlertDetector = $this->app->make(UnauthorizedClientAlertDetector::class);
    $alerts = $unauthorizedClientAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
