<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\ClientUrlFactory;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

test('create appends the default path to the url of a site without a path of its own', function (): void {
    $defaultPath = sprintf('/%s', fake()->slug());
    $siteReport = SiteReport::factory()->create(['path' => null]);

    $clientUrlFactory = new ClientUrlFactory($defaultPath);
    $clientUrl = $clientUrlFactory->create($siteReport);

    expect($clientUrl)
        ->toBe(sprintf('%s%s', rtrim($siteReport->site->url, '/'), $defaultPath));
});

test('create appends the path of the site', function (): void {
    $siteReport = SiteReport::factory()->create(['path' => sprintf('/%s', fake()->slug())]);

    $clientUrlFactory = new ClientUrlFactory(sprintf('/%s', fake()->slug()));
    $clientUrl = $clientUrlFactory->create($siteReport);

    expect($clientUrl)
        ->toBe(sprintf('%s%s', rtrim($siteReport->site->url, '/'), $siteReport->path));
});

test('create joins a url ending in a slash with one slash', function (): void {
    $host = fake()->domainName();
    $clientPath = fake()->slug();
    $siteReport = SiteReport::factory()
        ->for(Site::factory()->state(['url' => sprintf('https://%s/', $host)]))
        ->create(['path' => sprintf('/%s', $clientPath)]);

    $clientUrlFactory = new ClientUrlFactory(sprintf('/%s', fake()->slug()));
    $clientUrl = $clientUrlFactory->create($siteReport);

    expect($clientUrl)
        ->toBe(sprintf('https://%s/%s', $host, $clientPath));
});
