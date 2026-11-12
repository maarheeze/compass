<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertAction;
use Maarheeze\Compass\Alerts\AlertActionClassifier;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\Alert;

test('classify is open for an active condition without a stored alert', function (): void {
    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::ACTIVE, null);

    expect($alertAction)
        ->toBe(AlertAction::OPEN);
});

test('classify is nothing for an active condition with an active alert', function (): void {
    $storedAlert = Alert::factory()
        ->active()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::ACTIVE, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});

test('classify is resolve for a resolved condition with an active alert', function (): void {
    $storedAlert = Alert::factory()
        ->active()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::RESOLVED, $storedAlert);

    expect($alertAction)
        ->toBe(AlertAction::RESOLVE);
});

test('classify is nothing for a resolved condition without a stored alert', function (): void {
    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::RESOLVED, null);

    expect($alertAction)
        ->toBeNull();
});

test('classify is nothing for a resolved condition with a resolved alert', function (): void {
    $storedAlert = Alert::factory()
        ->resolved()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::RESOLVED, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});

test('classify is recur for an active condition with a resolved alert', function (): void {
    $storedAlert = Alert::factory()
        ->resolved()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::ACTIVE, $storedAlert);

    expect($alertAction)
        ->toBe(AlertAction::RECUR);
});

test('classify is nothing for a not applicable condition without a stored alert', function (): void {
    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::NOT_APPLICABLE, null);

    expect($alertAction)
        ->toBeNull();
});

test('classify is resolve for a not applicable condition with an active alert', function (): void {
    $storedAlert = Alert::factory()
        ->active()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::NOT_APPLICABLE, $storedAlert);

    expect($alertAction)
        ->toBe(AlertAction::RESOLVE);
});

test('classify is nothing for a not applicable condition with a resolved alert', function (): void {
    $storedAlert = Alert::factory()
        ->resolved()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::NOT_APPLICABLE, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});
