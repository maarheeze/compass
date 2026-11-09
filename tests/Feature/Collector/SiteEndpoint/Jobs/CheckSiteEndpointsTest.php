<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoint;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Uuid\Uuid;

test('handle adds the check of an endpoint of a collected site to its batch', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    [$checkSiteEndpoints, $batchFake] = new CheckSiteEndpoints()->withFakeBatch();
    $this->app->call([$checkSiteEndpoints, 'handle']);

    $addedSiteEndpointIds = array_map(
        static function (CheckSiteEndpoint $checkSiteEndpoint): Uuid {
            return $checkSiteEndpoint->siteEndpoint->id;
        },
        $batchFake->added,
    );

    expect($addedSiteEndpointIds)
        ->toEqual([$siteEndpoint->id]);
});

test('handle leaves out the endpoints of a missing site', function (): void {
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => fake()->dateTime()])))
        ->create();

    [$checkSiteEndpoints, $batchFake] = new CheckSiteEndpoints()->withFakeBatch();
    $this->app->call([$checkSiteEndpoints, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('lock key is the site endpoints task', function (): void {
    $checkSiteEndpoints = new CheckSiteEndpoints();
    $lockKey = $checkSiteEndpoints->lockKey();

    expect($lockKey)
        ->toBe(TaskType::SITE_ENDPOINTS->value);
});
