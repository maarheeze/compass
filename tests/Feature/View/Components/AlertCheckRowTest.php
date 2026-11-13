<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\View\Components\AlertCheckRow;

test('render renders the alert check row view', function (): void {
    $alertCheck = AlertCheck::factory()->create();

    $alertCheckRow = $this->app->make(AlertCheckRow::class, ['alertCheck' => $alertCheck]);

    expect($alertCheckRow->render()->name())
        ->toBe('compass::components.alert-check-row');
});
