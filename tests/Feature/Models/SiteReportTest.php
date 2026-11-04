<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\SiteReport;

test('display name is the site name followed by the source', function (): void {
    $siteReport = SiteReport::factory()->create();

    $displayName = $siteReport->displayName();

    expect($displayName)
        ->toBe(sprintf('%s (%s)', $siteReport->site->name, $siteReport->source));
});
