<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\SiteEndpoint\EndpointCheckHistory;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('clear deletes the checks of the path of the site', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->create();

    $endpointCheckHistory = $this->app->make(EndpointCheckHistory::class);
    $endpointCheckHistory->clear($siteEndpointCheck->site, $siteEndpointCheck->path);

    $this->assertModelMissing($siteEndpointCheck);
});

test('clear makes the endpoint of the path due at once', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $siteEndpoint = SiteEndpoint::factory()->create(['due_at' => $now->addMinutes(fake()->numberBetween(1, 60))]);

    $endpointCheckHistory = $this->app->make(EndpointCheckHistory::class);
    $endpointCheckHistory->clear($siteEndpoint->siteReport->site, $siteEndpoint->path);

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'id' => $siteEndpoint->id,
        'due_at' => $now,
    ]);
});

test('clear leaves the due time of the endpoint of another site', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $dueAt = $now->addMinutes(fake()->numberBetween(1, 60));
    $siteEndpoint = SiteEndpoint::factory()->create(['due_at' => $dueAt]);

    $endpointCheckHistory = $this->app->make(EndpointCheckHistory::class);
    $endpointCheckHistory->clear(Site::factory()->create(), $siteEndpoint->path);

    $this->assertDatabaseHas(SiteEndpoint::class, [
        'id' => $siteEndpoint->id,
        'due_at' => $dueAt,
    ]);
});
