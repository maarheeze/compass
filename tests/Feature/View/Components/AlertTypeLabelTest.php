<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertTypeLabel;

test('label is the translated label of the type of the alert', function (): void {
    $alert = Alert::factory()
        ->siteEndpointDown()
        ->create();

    $alertTypeLabel = $this->app->make(AlertTypeLabel::class, ['alert' => $alert]);

    expect($alertTypeLabel->label)
        ->toBe('Endpoint down');
});
