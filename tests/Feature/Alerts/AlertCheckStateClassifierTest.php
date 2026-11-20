<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\Alerts\AlertCheckStateClassifier;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\AlertCheck;

test('classify is ok for a resolved check', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::RESOLVED]);
    $alertCheck->alert_is_muted = fake()->optional()->boolean();

    $alertCheckStateClassifier = $this->app->make(AlertCheckStateClassifier::class);
    $alertCheckState = $alertCheckStateClassifier->classify($alertCheck);

    expect($alertCheckState)
        ->toBe(AlertCheckState::OK);
});

test('classify is failing for an active check whose alert is not muted', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $alertCheck->alert_is_muted = false;

    $alertCheckStateClassifier = $this->app->make(AlertCheckStateClassifier::class);
    $alertCheckState = $alertCheckStateClassifier->classify($alertCheck);

    expect($alertCheckState)
        ->toBe(AlertCheckState::FAILING);
});

test('classify is failing for an active check without an open alert', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $alertCheck->alert_is_muted = null;

    $alertCheckStateClassifier = $this->app->make(AlertCheckStateClassifier::class);
    $alertCheckState = $alertCheckStateClassifier->classify($alertCheck);

    expect($alertCheckState)
        ->toBe(AlertCheckState::FAILING);
});

test('classify is muted for an active check whose alert is muted', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::ACTIVE]);
    $alertCheck->alert_is_muted = true;

    $alertCheckStateClassifier = $this->app->make(AlertCheckStateClassifier::class);
    $alertCheckState = $alertCheckStateClassifier->classify($alertCheck);

    expect($alertCheckState)
        ->toBe(AlertCheckState::MUTED);
});

test('classify refuses a check that does not apply', function (): void {
    $alertCheck = AlertCheck::factory()->make(['condition' => ConditionState::NOT_APPLICABLE]);

    $alertCheckStateClassifier = $this->app->make(AlertCheckStateClassifier::class);
    $alertCheckStateClassifier->classify($alertCheck);
})->throws(LogicException::class);
