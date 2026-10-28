<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertAction;
use Maarheeze\Compass\Alerts\AlertActionClassifier;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\Alert;

test('classify is open for a present condition without a stored alert', function (): void {
    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::PRESENT, null);

    expect($alertAction)
        ->toBe(AlertAction::OPEN);
});

test('classify is nothing for a present condition with a present alert', function (): void {
    $storedAlert = Alert::factory()
        ->present()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::PRESENT, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});

test('classify is clear for a gone condition with a present alert', function (): void {
    $storedAlert = Alert::factory()
        ->present()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::GONE, $storedAlert);

    expect($alertAction)
        ->toBe(AlertAction::CLEAR);
});

test('classify is nothing for a gone condition without a stored alert', function (): void {
    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::GONE, null);

    expect($alertAction)
        ->toBeNull();
});

test('classify is nothing for a gone condition with a gone alert', function (): void {
    $storedAlert = Alert::factory()
        ->gone()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::GONE, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});

test('classify is recur for a present condition with a gone alert', function (): void {
    $storedAlert = Alert::factory()
        ->gone()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(ConditionState::PRESENT, $storedAlert);

    expect($alertAction)
        ->toBe(AlertAction::RECUR);
});
