<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\Jobs\InspectSite;
use Maarheeze\Compass\Collector\Client\Jobs\InspectSites;
use Maarheeze\Compass\Models\Site;

test('handle adds the inspection of a collected site to its batch', function (): void {
    $site = Site::factory()->create();

    [$inspectSites, $batchFake] = new InspectSites()->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    $addedSiteIds = array_map(
        static function (InspectSite $inspectSite): int {
            return $inspectSite->site->id;
        },
        $batchFake->added,
    );

    expect($addedSiteIds)
        ->toBe([$site->id]);
});

test('handle leaves out a missing site', function (): void {
    Site::factory()->create(['missing_at' => fake()->dateTime()]);

    [$inspectSites, $batchFake] = new InspectSites()->withFakeBatch();
    $this->app->call([$inspectSites, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});
