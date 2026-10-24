<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\ComposerWarningAlertDetector;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('find alerts finds a repository with a composer warning', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);

    $composerWarningAlertDetector = $this->app->make(ComposerWarningAlertDetector::class);
    $alerts = $composerWarningAlertDetector->findAlerts();

    expect($alerts->sole()->repository->is($repository))
        ->toBeTrue();
});

test('find alerts leaves out a repository without a composer warning', function (): void {
    Repository::factory()->create(['composer_json_oid' => null]);

    $composerWarningAlertDetector = $this->app->make(ComposerWarningAlertDetector::class);
    $alerts = $composerWarningAlertDetector->findAlerts();

    expect($alerts)
        ->toBeEmpty();
});
