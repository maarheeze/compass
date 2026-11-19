<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('alerts including children keeps an alert of a report of the site', function (): void {
    $siteReport = SiteReport::factory()->create();
    $siteReportAlert = Alert::factory()
        ->siteDebugEnabled()
        ->for($siteReport, 'subject')
        ->create();

    $alertsIncludingChildren = $siteReport->site
        ->alertsIncludingChildren()
        ->get();

    expect($alertsIncludingChildren->sole()->is($siteReportAlert))
        ->toBeTrue();
});

test('latest deployment time is the end of the deployment once it has ended', function (): void {
    $site = Site::factory()->make([
        'deployment_started_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        'deployment_ended_at' => fake()->dateTimeBetween('-1 month'),
    ]);

    $latestDeploymentTime = $site->latestDeploymentTime();

    expect($latestDeploymentTime)
        ->toEqual($site->deployment_ended_at);
});

test('latest deployment time is the start of the deployment while it has not ended', function (): void {
    $site = Site::factory()->make([
        'deployment_started_at' => fake()->dateTimeBetween('-1 month'),
        'deployment_ended_at' => null,
    ]);

    $latestDeploymentTime = $site->latestDeploymentTime();

    expect($latestDeploymentTime)
        ->toEqual($site->deployment_started_at);
});

test('latest deployment time is null for a site that was never deployed', function (): void {
    $site = Site::factory()->make([
        'deployment_started_at' => null,
        'deployment_ended_at' => null,
    ]);

    $latestDeploymentTime = $site->latestDeploymentTime();

    expect($latestDeploymentTime)
        ->toBeNull();
});
