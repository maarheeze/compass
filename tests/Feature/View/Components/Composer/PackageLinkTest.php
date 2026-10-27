<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\Composer\PackageLink;

test('url is the compass page of the package', function (): void {
    $packageName = sprintf('%s/%s', fake()->slug(), fake()->slug());

    $packageLink = $this->app->make(PackageLink::class, ['packageName' => $packageName]);

    expect($packageLink->url)
        ->toBe(route(RouteName::GITHUB_PACKAGE, $packageName));
});

test('render renders the package link view', function (): void {
    $packageName = sprintf('%s/%s', fake()->slug(), fake()->slug());

    $packageLink = $this->app->make(PackageLink::class, ['packageName' => $packageName]);

    expect($packageLink->render()->name())
        ->toBe('compass::components.composer.package-link');
});
