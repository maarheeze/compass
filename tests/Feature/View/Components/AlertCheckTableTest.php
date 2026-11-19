<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\View\Components\AlertCheckTable;

test('failing alert checks are the checks that fail', function (): void {
    $failingAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $failingAlertCheck->alert_is_muted = false;
    $okAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);

    $alertCheckTable = $this->app->make(AlertCheckTable::class, [
        'alertChecks' => new AlertCheckCollection([$failingAlertCheck, $okAlertCheck]),
    ]);

    expect($alertCheckTable->failingAlertChecks->sole())
        ->toBe($failingAlertCheck);
});

test('not failing alert checks are the checks that do not fail', function (): void {
    $failingAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $failingAlertCheck->alert_is_muted = false;
    $okAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);

    $alertCheckTable = $this->app->make(AlertCheckTable::class, [
        'alertChecks' => new AlertCheckCollection([$failingAlertCheck, $okAlertCheck]),
    ]);

    expect($alertCheckTable->notFailingAlertChecks->sole())
        ->toBe($okAlertCheck);
});

test('render renders the alert check table view', function (): void {
    $alertCheckTable = $this->app->make(AlertCheckTable::class, ['alertChecks' => new AlertCheckCollection()]);

    expect($alertCheckTable->render()->name())
        ->toBe('compass::components.alert-check-table');
});
