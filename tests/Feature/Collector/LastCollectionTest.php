<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\LastCollection;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('collected at is the older of the latest repository and site collection', function (): void {
    $olderCollectedAt = CarbonImmutable::instance(fake()->dateTimeBetween('-2 months', '-1 month'));
    $newerCollectedAt = CarbonImmutable::instance(fake()->dateTimeBetween('-1 month'));
    Repository::factory()->create(['collected_at' => $olderCollectedAt]);
    Site::factory()->create(['collected_at' => $newerCollectedAt]);

    $lastCollection = $this->app->make(LastCollection::class);
    $collectedAt = $lastCollection->collectedAt();

    expect($collectedAt?->getTimestamp())
        ->toBe($olderCollectedAt->getTimestamp());
});

test('collected at is the latest repository collection when no site was collected', function (): void {
    $repositoryCollectedAt = CarbonImmutable::instance(fake()->dateTime());
    Repository::factory()->create(['collected_at' => $repositoryCollectedAt]);

    $lastCollection = $this->app->make(LastCollection::class);
    $collectedAt = $lastCollection->collectedAt();

    expect($collectedAt?->getTimestamp())
        ->toBe($repositoryCollectedAt->getTimestamp());
});

test('collected at is the latest site collection when no repository was collected', function (): void {
    $siteCollectedAt = CarbonImmutable::instance(fake()->dateTime());
    Site::factory()->create(['collected_at' => $siteCollectedAt]);

    $lastCollection = $this->app->make(LastCollection::class);
    $collectedAt = $lastCollection->collectedAt();

    expect($collectedAt?->getTimestamp())
        ->toBe($siteCollectedAt->getTimestamp());
});

test('collected at is null when nothing was collected', function (): void {
    $lastCollection = $this->app->make(LastCollection::class);

    expect($lastCollection->collectedAt())
        ->toBeNull();
});
