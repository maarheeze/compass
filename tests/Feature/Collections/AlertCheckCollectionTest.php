<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\Alerts\AlertCheckStateClassifier;
use Maarheeze\Compass\Alerts\AlertCheckStateCount;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Collections\AlertCheckCollection;
use Maarheeze\Compass\Models\AlertCheck;

test('failing is the checks that fail', function (): void {
    $failingAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $failingAlertCheck->alert_is_muted = false;
    $mutedAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $mutedAlertCheck->alert_is_muted = true;
    $okAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);
    $alertChecks = new AlertCheckCollection([$failingAlertCheck, $mutedAlertCheck, $okAlertCheck]);

    $failingAlertChecks = $alertChecks->failing($this->app->make(AlertCheckStateClassifier::class));

    expect($failingAlertChecks->sole())
        ->toBe($failingAlertCheck);
});

test('not failing is the checks that are muted or ok', function (): void {
    $failingAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $failingAlertCheck->alert_is_muted = false;
    $mutedAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $mutedAlertCheck->alert_is_muted = true;
    $okAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);
    $alertChecks = new AlertCheckCollection([$failingAlertCheck, $mutedAlertCheck, $okAlertCheck]);

    $notFailingAlertChecks = $alertChecks->notFailing($this->app->make(AlertCheckStateClassifier::class));

    expect($notFailingAlertChecks->values()->all())
        ->toBe([$mutedAlertCheck, $okAlertCheck]);
});

test('state counts count the checks per state, most urgent first', function (): void {
    $okAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);
    $otherOkAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);
    $failingAlertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $failingAlertCheck->alert_is_muted = false;
    $alertChecks = new AlertCheckCollection([$okAlertCheck, $failingAlertCheck, $otherOkAlertCheck]);

    $stateCounts = $alertChecks->stateCounts($this->app->make(AlertCheckStateClassifier::class));

    expect($stateCounts->all())
        ->toEqual([new AlertCheckStateCount(AlertCheckState::FAILING, 1), new AlertCheckStateCount(AlertCheckState::OK, 2)]);
});

test('state counts are empty without checks', function (): void {
    $alertChecks = new AlertCheckCollection();

    $stateCounts = $alertChecks->stateCounts($this->app->make(AlertCheckStateClassifier::class));

    expect($stateCounts)
        ->toBeEmpty();
});
