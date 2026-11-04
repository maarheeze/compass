<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\SiteEndpoint;

test('display name is the site name followed by the path', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $displayName = $siteEndpoint->displayName();

    expect($displayName)
        ->toBe(sprintf('%s%s', $siteEndpoint->siteReport->site->name, $siteEndpoint->path));
});
