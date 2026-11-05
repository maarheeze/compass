<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\ClientKey;
use Maarheeze\Compass\Models\SiteReport;

test('for site report is the key of the site report', function (): void {
    $siteReport = SiteReport::factory()->make(['key' => fake()->unique()->sha1()]);

    $clientKey = new ClientKey(fake()->unique()->sha1());
    $keyForSiteReport = $clientKey->forSiteReport($siteReport);

    expect($keyForSiteReport)
        ->toBe($siteReport->key);
});

test('for site report is the default key for a site report without its own', function (): void {
    $defaultKey = fake()->sha1();

    $clientKey = new ClientKey($defaultKey);
    $keyForSiteReport = $clientKey->forSiteReport(SiteReport::factory()->make(['key' => null]));

    expect($keyForSiteReport)
        ->toBe($defaultKey);
});
