<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\View\Components\SiteEndpointCheck\Details;

test('render renders the site endpoint check details view', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->make();

    $details = $this->app->make(Details::class, ['siteEndpointCheck' => $siteEndpointCheck]);

    expect($details->render()->name())
        ->toBe('compass::components.site-endpoint-check.details');
});

test('online since is when the endpoint came online before the check', function (): void {
    $createdAt = CarbonImmutable::parse(fake()->iso8601());
    $onlineSince = $createdAt->subMinutes(fake()->numberBetween(1, 60));
    $siteEndpointCheck = SiteEndpointCheck::factory()->make([
        'created_at' => $createdAt,
        'online_since' => $onlineSince,
    ]);

    $details = $this->app->make(Details::class, ['siteEndpointCheck' => $siteEndpointCheck]);

    expect($details->onlineSince)
        ->toEqual($onlineSince);
});

test('online since is empty when the endpoint came online at the check', function (): void {
    $createdAt = CarbonImmutable::parse(fake()->iso8601());
    $siteEndpointCheck = SiteEndpointCheck::factory()->make([
        'created_at' => $createdAt,
        'online_since' => $createdAt,
    ]);

    $details = $this->app->make(Details::class, ['siteEndpointCheck' => $siteEndpointCheck]);

    expect($details->onlineSince)
        ->toBeNull();
});

test('online since is empty when the endpoint is not online', function (): void {
    $siteEndpointCheck = SiteEndpointCheck::factory()->make(['online_since' => null]);

    $details = $this->app->make(Details::class, ['siteEndpointCheck' => $siteEndpointCheck]);

    expect($details->onlineSince)
        ->toBeNull();
});
