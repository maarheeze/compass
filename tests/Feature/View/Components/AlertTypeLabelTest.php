<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\View\Components\AlertTypeLabel;

test('label is the translated label of the alert type with the identifier', function (): void {
    $alertTypeLabel = $this->app->make(AlertTypeLabel::class, ['identifier' => new SiteEndpointDown()->identifier()]);

    expect($alertTypeLabel->label)
        ->toBe('Endpoint down');
});
