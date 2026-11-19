<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\View\Components\AlertTable;

test('unmuted open alerts are the unmuted open alerts of the alerts', function (): void {
    $alerts = new AlertCollection([
        Alert::factory()
            ->active()
            ->make(),
        Alert::factory()
            ->active()
            ->muted()
            ->make(),
        Alert::factory()
            ->done()
            ->make(),
    ]);

    $alertTable = $this->app->make(AlertTable::class, ['alerts' => $alerts]);

    expect($alertTable->unmutedOpenAlerts->all())
        ->toBe($alerts->unmutedOpen()->all());
});

test('muted or done alerts are the muted or done alerts of the alerts', function (): void {
    $alerts = new AlertCollection([
        Alert::factory()
            ->active()
            ->make(),
        Alert::factory()
            ->active()
            ->muted()
            ->make(),
        Alert::factory()
            ->done()
            ->make(),
    ]);

    $alertTable = $this->app->make(AlertTable::class, ['alerts' => $alerts]);

    expect($alertTable->mutedOrDoneAlerts->all())
        ->toBe($alerts->mutedOrDone()->all());
});

test('alert level counts are the level counts of the unmuted open alerts', function (): void {
    $alerts = new AlertCollection([
        Alert::factory()
            ->active()
            ->siteEndpointDown()
            ->make(),
        Alert::factory()
            ->active()
            ->muted()
            ->siteEndpointDown()
            ->make(),
        Alert::factory()
            ->done()
            ->siteEndpointDown()
            ->make(),
    ]);
    $alertTypes = $this->app->make(AlertTypes::class);

    $alertTable = $this->app->make(AlertTable::class, ['alerts' => $alerts]);

    expect($alertTable->alertLevelCounts->all())
        ->toEqual($alerts->unmutedOpen()
            ->levelCounts($alertTypes)
            ->all());
});

test('muted open alert count is the count of the muted open alerts', function (): void {
    $alerts = new AlertCollection([
        Alert::factory()
            ->active()
            ->make(),
        Alert::factory()
            ->active()
            ->muted()
            ->make(),
        Alert::factory()
            ->done()
            ->make(),
    ]);

    $alertTable = $this->app->make(AlertTable::class, ['alerts' => $alerts]);

    expect($alertTable->mutedOpenAlertCount)
        ->toBe($alerts->mutedOpen()->count());
});

test('done alert count is the count of the done alerts', function (): void {
    $alerts = new AlertCollection([
        Alert::factory()
            ->active()
            ->make(),
        Alert::factory()
            ->active()
            ->muted()
            ->make(),
        Alert::factory()
            ->done()
            ->make(),
    ]);

    $alertTable = $this->app->make(AlertTable::class, ['alerts' => $alerts]);

    expect($alertTable->doneAlertCount)
        ->toBe($alerts->done()->count());
});

test('render renders the alert table view', function (): void {
    $alertTable = $this->app->make(AlertTable::class, ['alerts' => new AlertCollection()]);

    expect($alertTable->render()->name())
        ->toBe('compass::components.alert-table');
});
