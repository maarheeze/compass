<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Client\NonProductionAlertDetector;
use Maarheeze\Compass\Models\Site;

test('find alerts finds a site outside production', function (): void {
    $site = Site::factory()->create(['app_env' => 'staging']);

    $nonProductionAlertDetector = $this->app->make(NonProductionAlertDetector::class);
    $alerts = $nonProductionAlertDetector->findAlerts();

    expect($alerts->sole()->site->is($site))
        ->toBeTrue();
});

test('find alerts leaves out a site in production', function (): void {
    Site::factory()->create(['app_env' => 'production']);

    $nonProductionAlertDetector = $this->app->make(NonProductionAlertDetector::class);
    $alerts = $nonProductionAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out a site without a report', function (): void {
    Site::factory()->create(['app_env' => null]);

    $nonProductionAlertDetector = $this->app->make(NonProductionAlertDetector::class);
    $alerts = $nonProductionAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out a missing site', function (): void {
    Site::factory()->create([
        'app_env' => 'staging',
        'missing_at' => fake()->dateTime(),
    ]);

    $nonProductionAlertDetector = $this->app->make(NonProductionAlertDetector::class);
    $alerts = $nonProductionAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
