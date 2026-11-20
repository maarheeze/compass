<?php

declare(strict_types=1);

use Illuminate\Config\Repository as Config;
use Maarheeze\Compass\View\Components\PackageVersions;

test('render renders the package versions view', function (): void {
    $packageVersions = $this->app->make(PackageVersions::class);

    expect($packageVersions->render()->name())
        ->toBe('compass::components.package-versions');
});

test('package names are the configured dashboard packages', function (): void {
    $packageNames = [sprintf('%s/%s', fake()->slug(), fake()->slug())];
    $this->app->make(Config::class)->set('compass.dashboard_packages', $packageNames);

    $packageVersions = $this->app->make(PackageVersions::class);

    expect($packageVersions->packageNames)
        ->toBe($packageNames);
});

test('package names refuse a dashboard package that is not a string', function (): void {
    $this->app->make(Config::class)->set('compass.dashboard_packages', [fake()->randomNumber()]);

    $this->app->make(PackageVersions::class);
})->throws(InvalidArgumentException::class);

test('should render with dashboard packages', function (): void {
    $this->app->make(Config::class)->set('compass.dashboard_packages', [sprintf('%s/%s', fake()->slug(), fake()->slug())]);

    $packageVersions = $this->app->make(PackageVersions::class);

    expect($packageVersions->shouldRender())
        ->toBeTrue();
});

test('should not render without dashboard packages', function (): void {
    $this->app->make(Config::class)->set('compass.dashboard_packages', []);

    $packageVersions = $this->app->make(PackageVersions::class);

    expect($packageVersions->shouldRender())
        ->toBeFalse();
});
