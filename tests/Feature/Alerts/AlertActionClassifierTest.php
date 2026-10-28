<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertAction;
use Maarheeze\Compass\Alerts\AlertActionClassifier;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Models\Alert;

test('classify is open for a present condition without a stored alert', function (): void {
    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(AlertCondition::PRESENT, null);

    expect($alertAction)
        ->toBe(AlertAction::OPEN);
});

test('classify is nothing for a present condition with a present alert', function (): void {
    $storedAlert = Alert::factory()
        ->present()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(AlertCondition::PRESENT, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});

test('classify is clear for a gone condition with a present alert', function (): void {
    $storedAlert = Alert::factory()
        ->present()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(AlertCondition::GONE, $storedAlert);

    expect($alertAction)
        ->toBe(AlertAction::CLEAR);
});

test('classify is nothing for a gone condition without a stored alert', function (): void {
    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(AlertCondition::GONE, null);

    expect($alertAction)
        ->toBeNull();
});

test('classify is nothing for a gone condition with a gone alert', function (): void {
    $storedAlert = Alert::factory()
        ->gone()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(AlertCondition::GONE, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});

test('classify is nothing for a present condition with a gone alert', function (): void {
    $storedAlert = Alert::factory()
        ->gone()
        ->make();

    $alertActionClassifier = $this->app->make(AlertActionClassifier::class);
    $alertAction = $alertActionClassifier->classify(AlertCondition::PRESENT, $storedAlert);

    expect($alertAction)
        ->toBeNull();
});
