<?php

declare(strict_types=1);

use Illuminate\Config\Repository as Config;
use Maarheeze\Compass\View\Components\ApplicationName;

test('application name is the configured application name', function (): void {
    $applicationName = fake()->company();
    $this->app->make(Config::class)->set('app.name', $applicationName);

    $applicationNameComponent = $this->app->make(ApplicationName::class);

    expect($applicationNameComponent->applicationName)
        ->toBe($applicationName);
});
