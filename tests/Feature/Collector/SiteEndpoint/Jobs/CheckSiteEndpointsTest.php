<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoint;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
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
        ->for(Site::factory()->state(['missing_at' => fake()->dateTime()]))
        ->create();

    [$checkSiteEndpoints, $batchFake] = new CheckSiteEndpoints()->withFakeBatch();
    $this->app->call([$checkSiteEndpoints, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});
