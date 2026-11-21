<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\SiteMaintenanceModeBadge;

test('renders for a site with a report in maintenance mode', function (): void {
    $site = Site::factory()->create();
    SiteReport::factory()->for($site)->create(['maintenance_mode' => true]);

    $siteMaintenanceModeBadge = $this->app->make(SiteMaintenanceModeBadge::class, ['site' => $site]);

    expect($siteMaintenanceModeBadge->shouldRender())
        ->toBeTrue();
});

test('does not render for a site without a report in maintenance mode', function (): void {
    $site = Site::factory()->create();
    SiteReport::factory()->for($site)->create(['maintenance_mode' => false]);

    $siteMaintenanceModeBadge = $this->app->make(SiteMaintenanceModeBadge::class, ['site' => $site]);

    expect($siteMaintenanceModeBadge->shouldRender())
        ->toBeFalse();
});
