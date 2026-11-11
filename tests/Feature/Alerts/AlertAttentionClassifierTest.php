<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertAttention;
use Maarheeze\Compass\Alerts\AlertAttentionClassifier;
use Maarheeze\Compass\Models\Alert;

test('classify is none for only done alerts', function (): void {
    Alert::factory()
        ->done()
        ->create();

    $alertAttentionClassifier = $this->app->make(AlertAttentionClassifier::class);
    $alertAttention = $alertAttentionClassifier->classify(Alert::query());

    expect($alertAttention)
        ->toBe(AlertAttention::NONE);
});

test('classify is muted for only muted alerts that are not done', function (): void {
    Alert::factory()
        ->active()
        ->muted()
        ->create();

    $alertAttentionClassifier = $this->app->make(AlertAttentionClassifier::class);
    $alertAttention = $alertAttentionClassifier->classify(Alert::query());

    expect($alertAttention)
        ->toBe(AlertAttention::MUTED);
});

test('classify is muted for a muted alert next to a done alert that is not muted', function (): void {
    Alert::factory()
        ->active()
        ->muted()
        ->create();
    Alert::factory()
        ->done()
        ->create();

    $alertAttentionClassifier = $this->app->make(AlertAttentionClassifier::class);
    $alertAttention = $alertAttentionClassifier->classify(Alert::query());

    expect($alertAttention)
        ->toBe(AlertAttention::MUTED);
});

test('classify is active for an alert that is not done and not muted', function (): void {
    Alert::factory()
        ->active()
        ->create();

    $alertAttentionClassifier = $this->app->make(AlertAttentionClassifier::class);
    $alertAttention = $alertAttentionClassifier->classify(Alert::query());

    expect($alertAttention)
        ->toBe(AlertAttention::ACTIVE);
});
