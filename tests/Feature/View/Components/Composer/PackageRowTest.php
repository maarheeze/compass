<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\Composer\PackageRow;

test('url is the compass page of the package', function (): void {
    $packageName = sprintf('%s/%s', fake()->slug(), fake()->slug());

    $packageRow = $this->app->make(PackageRow::class, ['packageName' => $packageName]);

    expect($packageRow->url)
        ->toBe(route(RouteName::PACKAGE, $packageName));
});

test('render renders the package row view', function (): void {
    $packageName = sprintf('%s/%s', fake()->slug(), fake()->slug());

    $packageRow = $this->app->make(PackageRow::class, ['packageName' => $packageName]);

    expect($packageRow->render()->name())
        ->toBe('compass::components.composer.package-row');
});
