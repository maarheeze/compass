<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

test('handle adds the inspection of a collected site to its batch', function (): void {
    $site = Site::factory()->create();

    [$inspectSites, $batchFake] = new InspectSites(Uuid::generate(), fake()->slug())->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    $addedSiteIds = array_map(
        static function (InspectSite $inspectSite): Uuid {
            return $inspectSite->site->id;
        },
        $batchFake->added,
    );

    expect($addedSiteIds)
        ->toEqual([$site->id]);
});

test('handle hands its source to the inspection of a site', function (): void {
    $sourceKey = fake()->slug();
    Site::factory()->create();

    [$inspectSites, $batchFake] = new InspectSites(Uuid::generate(), $sourceKey)->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    $addedSourceKeys = array_map(
        static function (InspectSite $inspectSite): string {
            return $inspectSite->sourceKey;
        },
        $batchFake->added,
    );

    expect($addedSourceKeys)
        ->toBe([$sourceKey]);
});

test('handle leaves out a missing site', function (): void {
    Site::factory()->create(['missing_at' => fake()->dateTime()]);

    [$inspectSites, $batchFake] = new InspectSites(Uuid::generate(), fake()->slug())->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});
