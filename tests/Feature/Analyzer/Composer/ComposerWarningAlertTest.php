<?php

declare(strict_types=1);

use Illuminate\Contracts\View\Factory as ViewFactory;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningAlert;
use Maarheeze\Compass\Models\Repository;

test('view name names an existing view', function (): void {
    $viewFactory = $this->app->make(ViewFactory::class);

    $alert = new ComposerWarningAlert(Repository::factory()->make());

    expect($viewFactory->exists($alert->viewName()))
        ->toBeTrue();
});
