<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\CollectorRegistry;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\GitHub\Jobs\CollectIssues;
use Maarheeze\Uuid\Uuid;

test('job is the registered job of the collector type', function (): void {
    $collectorType = fake()->randomElement(CollectorType::cases());

    $collectorRegistry = new CollectorRegistry();
    $collectorRegistry->register($collectorType, CollectIssues::class);

    expect($collectorRegistry->job($collectorType, Uuid::generate()))
        ->toBeInstanceOf(CollectIssues::class);
});
