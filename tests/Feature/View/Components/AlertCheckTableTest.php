<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\View\Components\AlertCheckTable;

test('render renders the alert check table view', function (): void {
    $alertCheckTable = $this->app->make(AlertCheckTable::class, ['alertChecks' => new AlertCheckCollection()]);

    expect($alertCheckTable->render()->name())
        ->toBe('compass::components.alert-check-table');
});
