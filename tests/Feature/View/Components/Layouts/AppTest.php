<?php

declare(strict_types=1);

use Illuminate\Config\Repository as Config;
use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\View\Components\Layouts\App;

test('application name is the configured application name', function (): void {
    $applicationName = fake()->company();
    $this->app->make(Config::class)->set('app.name', $applicationName);

    $appLayout = $this->app->make(App::class);

    expect($appLayout->applicationName)
        ->toBe($applicationName);
});

test('html language is the locale with dashes', function (): void {
    $this->app->make(Translator::class)->setLocale('en_GB');

    $appLayout = $this->app->make(App::class);

    expect($appLayout->htmlLanguage)
        ->toBe('en-GB');
});
