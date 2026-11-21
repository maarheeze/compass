<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Http\SiteFilter;
use Maarheeze\Compass\Models\Site;

test('sites without a filter are all the sites', function (): void {
    $sites = Site::factory()
        ->count(2)
        ->create();
    $siteFilter = new SiteFilter(null, null);

    $filteredSites = $siteFilter->sites(Site::query())->get();

    expect($filteredSites->modelKeys())
        ->toEqualCanonicalizing($sites->modelKeys());
});

test('sites on a server and a php version are the sites on both', function (): void {
    $serverName = fake()->unique()->slug();
    $matchingSite = Site::factory()->create([
        'server_name' => $serverName,
        'php_version' => 'php84',
    ]);
    Site::factory()->create([
        'server_name' => $serverName,
        'php_version' => 'php83',
    ]);
    Site::factory()->create([
        'server_name' => fake()->unique()->slug(),
        'php_version' => 'php84',
    ]);
    $siteFilter = new SiteFilter($serverName, 'php84');

    $filteredSites = $siteFilter->sites(Site::query())->get();

    expect($filteredSites->sole()->is($matchingSite))
        ->toBeTrue();
});

test('php version filter is filtered on the php version', function (): void {
    $siteFilter = new SiteFilter(null, null);

    $phpVersionFilter = $siteFilter->phpVersionFilter(Site::query(), $this->app->make(Translator::class));

    expect($phpVersionFilter->parameterNames)
        ->toBe([SiteFilter::PHP_VERSION_PARAMETER]);
});

test('php version filter counts only the sites on the chosen server', function (): void {
    $serverName = fake()->unique()->slug();
    Site::factory()->create([
        'server_name' => $serverName,
        'php_version' => 'php84',
    ]);
    Site::factory()->create([
        'server_name' => fake()->unique()->slug(),
        'php_version' => 'php83',
    ]);
    $siteFilter = new SiteFilter($serverName, null);

    $phpVersionFilter = $siteFilter->phpVersionFilter(Site::query(), $this->app->make(Translator::class));

    expect($phpVersionFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::PHP_VERSION_PARAMETER => 'php84']);
});

test('php version filter counts the sites of every php version on a chosen php version', function (): void {
    Site::factory()->create(['php_version' => 'php84']);
    $siteFilter = new SiteFilter(null, 'php83');

    $phpVersionFilter = $siteFilter->phpVersionFilter(Site::query(), $this->app->make(Translator::class));

    expect($phpVersionFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::PHP_VERSION_PARAMETER => 'php84']);
});

test('server filter is filtered on the server', function (): void {
    $siteFilter = new SiteFilter(null, null);

    $serverFilter = $siteFilter->serverFilter(Site::query(), $this->app->make(Translator::class));

    expect($serverFilter->parameterNames)
        ->toBe([SiteFilter::SERVER_PARAMETER]);
});

test('server filter counts only the sites on the chosen php version', function (): void {
    $site = Site::factory()->create([
        'server_name' => fake()->unique()->slug(),
        'php_version' => 'php84',
    ]);
    Site::factory()->create([
        'server_name' => fake()->unique()->slug(),
        'php_version' => 'php83',
    ]);
    $siteFilter = new SiteFilter(null, 'php84');

    $serverFilter = $siteFilter->serverFilter(Site::query(), $this->app->make(Translator::class));

    expect($serverFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::SERVER_PARAMETER => $site->server_name]);
});

test('server filter counts the sites on every server on a chosen server', function (): void {
    $site = Site::factory()->create();
    $siteFilter = new SiteFilter(fake()->unique()->slug(), null);

    $serverFilter = $siteFilter->serverFilter(Site::query(), $this->app->make(Translator::class));

    expect($serverFilter->options->sole()->queryParameters)
        ->toBe([SiteFilter::SERVER_PARAMETER => $site->server_name]);
});
