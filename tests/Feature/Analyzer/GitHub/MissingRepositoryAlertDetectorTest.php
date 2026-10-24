<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\MissingRepositoryAlertDetector;
use Maarheeze\Compass\Models\Repository;

test('find alerts finds a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingRepositoryAlertDetector = $this->app->make(MissingRepositoryAlertDetector::class);
    $alerts = $missingRepositoryAlertDetector->findAlerts();

    expect($alerts->sole()->repository->is($repository))
        ->toBeTrue();
});

test('find alerts leaves out a collected repository', function (): void {
    Repository::factory()->create();

    $missingRepositoryAlertDetector = $this->app->make(MissingRepositoryAlertDetector::class);
    $alerts = $missingRepositoryAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});

test('find alerts leaves out a missing repository that is ignored', function (): void {
    Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingRepositoryAlertDetector = $this->app->make(MissingRepositoryAlertDetector::class);
    $alerts = $missingRepositoryAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
