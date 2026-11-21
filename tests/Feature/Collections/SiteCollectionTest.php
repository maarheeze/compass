<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Http\SiteFilter;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\FilterOption;

test('php version filter options count the sites per php version, newest first', function (): void {
    $sites = new SiteCollection([
        Site::factory()->make(['php_version' => 'php83']),
        Site::factory()->make(['php_version' => 'php84']),
        Site::factory()->make(['php_version' => 'php84']),
    ]);

    $phpVersionFilterOptions = $sites->phpVersionFilterOptions();

    expect($phpVersionFilterOptions->all())
        ->toEqual([
            new FilterOption('php84', 2, [SiteFilter::PHP_VERSION_PARAMETER => 'php84'], null),
            new FilterOption('php83', 1, [SiteFilter::PHP_VERSION_PARAMETER => 'php83'], null),
        ]);
});

test('php version filter options leave out the sites without a known php version', function (): void {
    $sites = new SiteCollection([
        Site::factory()->make(['php_version' => null]),
    ]);

    $phpVersionFilterOptions = $sites->phpVersionFilterOptions();

    expect($phpVersionFilterOptions)
        ->toBeEmpty();
});

test('server filter options count the sites per server in the order of the server name', function (): void {
    $sites = new SiteCollection([
        Site::factory()->make(['server_name' => 'web-2']),
        Site::factory()->make(['server_name' => 'web-10']),
        Site::factory()->make(['server_name' => 'web-2']),
    ]);

    $serverFilterOptions = $sites->serverFilterOptions();

    expect($serverFilterOptions->all())
        ->toEqual([
            new FilterOption('web-2', 2, [SiteFilter::SERVER_PARAMETER => 'web-2'], null),
            new FilterOption('web-10', 1, [SiteFilter::SERVER_PARAMETER => 'web-10'], null),
        ]);
});
