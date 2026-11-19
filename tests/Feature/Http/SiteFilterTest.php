<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Http\SiteFilter;
use Maarheeze\Compass\Models\Site;

test('sites without a filter are all the sites', function (): void {
    $sites = new SiteCollection([
        Site::factory()->make(),
        Site::factory()->make(),
    ]);
    $siteFilter = new SiteFilter(null, null);

    $filteredSites = $siteFilter->sites($sites);

    expect($filteredSites->all())
        ->toBe($sites->all());
});

test('sites on a server and a php version are the sites on both', function (): void {
    $serverName = fake()->unique()->slug();
    $matchingSite = Site::factory()->make([
        'server_name' => $serverName,
        'php_version' => 'php84',
    ]);
    $siteOnOtherPhpVersion = Site::factory()->make([
        'server_name' => $serverName,
        'php_version' => 'php83',
    ]);
    $siteOnOtherServer = Site::factory()->make([
        'server_name' => fake()->unique()->slug(),
        'php_version' => 'php84',
    ]);
    $siteFilter = new SiteFilter($serverName, 'php84');

    $filteredSites = $siteFilter->sites(new SiteCollection([$matchingSite, $siteOnOtherPhpVersion, $siteOnOtherServer]));

    expect($filteredSites->sole())
        ->toBe($matchingSite);
});

test('php version filter is filtered on the php version', function (): void {
    $siteFilter = new SiteFilter(null, null);

    $phpVersionFilter = $siteFilter->phpVersionFilter(new SiteCollection(), $this->app->make(Translator::class));

    expect($phpVersionFilter->parameterNames)
        ->toBe([SiteFilter::PHP_VERSION_PARAMETER]);
});

test('php version filter counts only the sites on the chosen server', function (): void {
    $serverName = fake()->unique()->slug();
    $sites = new SiteCollection([
        Site::factory()->make([
            'server_name' => $serverName,
            'php_version' => 'php84',
        ]),
        Site::factory()->make([
            'server_name' => fake()->unique()->slug(),
            'php_version' => 'php83',
        ]),
    ]);
    $siteFilter = new SiteFilter($serverName, null);

    $phpVersionFilter = $siteFilter->phpVersionFilter($sites, $this->app->make(Translator::class));

    expect($phpVersionFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::PHP_VERSION_PARAMETER => 'php84']);
});

test('php version filter counts the sites of every php version on a chosen php version', function (): void {
    $sites = new SiteCollection([
        Site::factory()->make(['php_version' => 'php84']),
    ]);
    $siteFilter = new SiteFilter(null, 'php83');

    $phpVersionFilter = $siteFilter->phpVersionFilter($sites, $this->app->make(Translator::class));

    expect($phpVersionFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::PHP_VERSION_PARAMETER => 'php84']);
});

test('server filter is filtered on the server', function (): void {
    $siteFilter = new SiteFilter(null, null);

    $serverFilter = $siteFilter->serverFilter(new SiteCollection(), $this->app->make(Translator::class));

    expect($serverFilter->parameterNames)
        ->toBe([SiteFilter::SERVER_PARAMETER]);
});

test('server filter counts only the sites on the chosen php version', function (): void {
    $site = Site::factory()->make([
        'server_name' => fake()->unique()->slug(),
        'php_version' => 'php84',
    ]);
    $sites = new SiteCollection([
        $site,
        Site::factory()->make([
            'server_name' => fake()->unique()->slug(),
            'php_version' => 'php83',
        ]),
    ]);
    $siteFilter = new SiteFilter(null, 'php84');

    $serverFilter = $siteFilter->serverFilter($sites, $this->app->make(Translator::class));

    expect($serverFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::SERVER_PARAMETER => $site->server_name]);
});

test('server filter counts the sites on every server on a chosen server', function (): void {
    $site = Site::factory()->make();
    $siteFilter = new SiteFilter(fake()->unique()->slug(), null);

    $serverFilter = $siteFilter->serverFilter(new SiteCollection([$site]), $this->app->make(Translator::class));

    expect($serverFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::SERVER_PARAMETER => $site->server_name]);
});
