<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Client\DebugEnabledAlertDetector;
use Maarheeze\Compass\Models\Site;

test('find alerts finds a site with debug mode on', function (): void {
    $site = Site::factory()->create(['app_debug' => true]);

    $debugEnabledAlertDetector = $this->app->make(DebugEnabledAlertDetector::class);
    $alerts = $debugEnabledAlertDetector->findAlerts();

    expect($alerts->sole()->site->is($site))
        ->toBeTrue();
});

test('find alerts leaves out a site with debug mode off', function (): void {
    Site::factory()->create(['app_debug' => false]);

    $debugEnabledAlertDetector = $this->app->make(DebugEnabledAlertDetector::class);
    $alerts = $debugEnabledAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out a site without a report', function (): void {
    Site::factory()->create(['app_debug' => null]);

    $debugEnabledAlertDetector = $this->app->make(DebugEnabledAlertDetector::class);
    $alerts = $debugEnabledAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out a missing site', function (): void {
    Site::factory()->create([
        'app_debug' => true,
        'missing_at' => fake()->dateTime(),
    ]);

    $debugEnabledAlertDetector = $this->app->make(DebugEnabledAlertDetector::class);
    $alerts = $debugEnabledAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
