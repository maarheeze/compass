<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoint;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('handle adds the check of a due endpoint of a collected site to its batch', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteEndpoint = SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => null])))
        ->create(['due_at' => $now->subMinutes(fake()->numberBetween(0, 60))]);

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

test('handle leaves out an endpoint that is not due yet', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => null])))
        ->create(['due_at' => $now->addMinutes(fake()->numberBetween(1, 60))]);

    [$checkSiteEndpoints, $batchFake] = new CheckSiteEndpoints()->withFakeBatch();
    $this->app->call([$checkSiteEndpoints, 'handle']);

    expect($batchFake->added)
        ->toBeEmpty();
});

test('handle leaves out the endpoints of a missing site', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    SiteEndpoint::factory()
        ->for(SiteReport::factory()->for(Site::factory()->state(['missing_at' => fake()->dateTime()])))
        ->create(['due_at' => $now->subMinutes(fake()->numberBetween(0, 60))]);

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
