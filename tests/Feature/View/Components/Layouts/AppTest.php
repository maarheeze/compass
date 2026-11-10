<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\View\Components\Layouts\App;

test('html language is the locale with dashes', function (): void {
    $this->app->make(Translator::class)->setLocale('en_GB');

    $appLayout = $this->app->make(App::class);

    expect($appLayout->htmlLanguage)
        ->toBe('en-GB');
});
