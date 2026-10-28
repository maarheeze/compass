<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\View\Components\AlertTable;

test('render renders the alert table view', function (): void {
    $alertTable = $this->app->make(AlertTable::class, ['alerts' => new AlertCollection()]);

    expect($alertTable->render()->name())
        ->toBe('compass::components.alert-table');
});
