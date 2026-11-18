<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyser\Composer\PackageVersionCount;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\PackageVersionRow;

test('render renders the package version row view', function (): void {
    $packageVersionRow = $this->app->make(PackageVersionRow::class, [
        'packageName' => sprintf('%s/%s', fake()->slug(), fake()->slug()),
        'versionCount' => new PackageVersionCount(fake()->semver(), fake()->numberBetween(1, 10)),
    ]);

    expect($packageVersionRow->render()->name())
        ->toBe('compass::components.package-version-row');
});

test('url is the compass page of the version of the package', function (): void {
    $packageName = sprintf('%s/%s', fake()->slug(), fake()->slug());
    $versionCount = new PackageVersionCount(fake()->semver(), fake()->numberBetween(1, 10));

    $packageVersionRow = $this->app->make(PackageVersionRow::class, [
        'packageName' => $packageName,
        'versionCount' => $versionCount,
    ]);

    expect($packageVersionRow->url)
        ->toBe(route(RouteName::PACKAGE_VERSION, ['packageName' => $packageName, 'version' => $versionCount->version]));
});
