<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Forge\MissingSiteAlertDetector;
use Maarheeze\Compass\Models\Site;

test('find alerts finds a missing site', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingSiteAlertDetector = $this->app->make(MissingSiteAlertDetector::class);
    $alerts = $missingSiteAlertDetector->findAlerts();

    expect($alerts->sole()->site->is($site))
        ->toBeTrue();
});

test('find alerts leaves out a collected site', function (): void {
    Site::factory()->create();

    $missingSiteAlertDetector = $this->app->make(MissingSiteAlertDetector::class);
    $alerts = $missingSiteAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out a missing site that is ignored', function (): void {
    Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingSiteAlertDetector = $this->app->make(MissingSiteAlertDetector::class);
    $alerts = $missingSiteAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
