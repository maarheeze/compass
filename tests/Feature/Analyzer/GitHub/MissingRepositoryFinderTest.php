<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\MissingRepositoryFinder;
use Maarheeze\Compass\Models\Repository;

test('find repositories finds a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingRepositoryFinder = $this->app->make(MissingRepositoryFinder::class);
    $missingRepositories = $missingRepositoryFinder->findRepositories();

    expect($missingRepositories->sole()->is($repository))
        ->toBeTrue();
});

test('find repositories leaves out a collected repository', function (): void {
    Repository::factory()->create();

    $missingRepositoryFinder = $this->app->make(MissingRepositoryFinder::class);
    $missingRepositories = $missingRepositoryFinder->findRepositories();

    expect($missingRepositories)
        ->toBeEmpty();
});

test('find repositories leaves out a missing repository that is ignored', function (): void {
    Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingRepositoryFinder = $this->app->make(MissingRepositoryFinder::class);
    $missingRepositories = $missingRepositoryFinder->findRepositories();

    expect($missingRepositories)
        ->toBeEmpty();
});
