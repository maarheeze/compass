<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\MinimumStabilityChecker;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

test('is stable is false for a minimum stability below stable without prefer stable', function (string $minimumStability): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'minimum_stability' => $minimumStability,
        'prefer_stable' => false,
    ]);

    $minimumStabilityChecker = $this->app->make(MinimumStabilityChecker::class);
    $isStable = $minimumStabilityChecker->isStable($repository);

    expect($isStable)
        ->toBeFalse();
})->with(['RC', 'beta', 'alpha', 'dev']);

test('is stable is true for a minimum stability below stable with prefer stable', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'minimum_stability' => fake()->randomElement(['RC', 'beta', 'alpha', 'dev']),
        'prefer_stable' => true,
    ]);

    $minimumStabilityChecker = $this->app->make(MinimumStabilityChecker::class);
    $isStable = $minimumStabilityChecker->isStable($repository);

    expect($isStable)
        ->toBeTrue();
});

test('is stable is true for a minimum stability of stable', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'minimum_stability' => 'stable',
        'prefer_stable' => false,
    ]);

    $minimumStabilityChecker = $this->app->make(MinimumStabilityChecker::class);
    $isStable = $minimumStabilityChecker->isStable($repository);

    expect($isStable)
        ->toBeTrue();
});

test('is stable is true without a minimum stability', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'minimum_stability' => null,
        'prefer_stable' => false,
    ]);

    $minimumStabilityChecker = $this->app->make(MinimumStabilityChecker::class);
    $isStable = $minimumStabilityChecker->isStable($repository);

    expect($isStable)
        ->toBeTrue();
});

test('is stable is true for a repository without a composer json', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => null]);

    $minimumStabilityChecker = $this->app->make(MinimumStabilityChecker::class);
    $isStable = $minimumStabilityChecker->isStable($repository);

    expect($isStable)
        ->toBeTrue();
});
