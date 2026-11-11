<?php

declare(strict_types=1);

use Illuminate\Config\Repository as Config;
use Maarheeze\Compass\View\Components\Brand;

test('brand shows the configured application name', function (): void {
    $applicationName = fake()->company();
    $this->app->make(Config::class)->set('app.name', $applicationName);

    $brand = $this->app->make(Brand::class);

    expect($brand->applicationName)
        ->toBe($applicationName);
});
