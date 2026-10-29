<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\ObservationCollection;
use Maarheeze\Compass\Models\Observation;

test('last observed at is the observed at of the latest observation', function (): void {
    $laterObservation = Observation::factory()->make([
        'observed_at' => fake()->dateTimeBetween('-1 month'),
    ]);
    $earlierObservation = Observation::factory()->make([
        'observed_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $observationCollection = new ObservationCollection([$earlierObservation, $laterObservation]);

    $lastObservedAt = $observationCollection->lastObservedAt();

    expect($lastObservedAt)
        ->toEqual($laterObservation->observed_at);
});

test('last observed at is null without observations', function (): void {
    $observationCollection = new ObservationCollection();

    $lastObservedAt = $observationCollection->lastObservedAt();

    expect($lastObservedAt)
        ->toBeNull();
});
