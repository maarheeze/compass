<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

test('collected keeps a collected repository', function (): void {
    $collectedRepository = Repository::factory()->create(['missing_at' => null]);

    $collectedRepositories = Repository::query()
        ->collected()
        ->get();

    expect($collectedRepositories->sole()->is($collectedRepository))
        ->toBeTrue();
});

test('collected leaves out a missing repository', function (): void {
    Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $collectedRepositories = Repository::query()
        ->collected()
        ->get();

    expect($collectedRepositories)
        ->toBeEmpty();
});

test('missing keeps a missing repository that is not ignored', function (): void {
    $missingRepository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $missingRepositories = Repository::query()
        ->missing()
        ->get();

    expect($missingRepositories->sole()->is($missingRepository))
        ->toBeTrue();
});

test('missing leaves out an ignored repository', function (): void {
    Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingRepositories = Repository::query()
        ->missing()
        ->get();

    expect($missingRepositories)
        ->toBeEmpty();
});

test('missing leaves out a collected repository', function (): void {
    Repository::factory()->create(['missing_at' => null]);

    $missingRepositories = Repository::query()
        ->missing()
        ->get();

    expect($missingRepositories)
        ->toBeEmpty();
});

test('not collected keeps a missing repository', function (): void {
    $notCollectedRepository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => null,
    ]);

    $notCollectedRepositories = Repository::query()
        ->notCollected()
        ->get();

    expect($notCollectedRepositories->sole()->is($notCollectedRepository))
        ->toBeTrue();
});

test('not collected keeps an ignored repository', function (): void {
    $notCollectedRepository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $notCollectedRepositories = Repository::query()
        ->notCollected()
        ->get();

    expect($notCollectedRepositories->sole()->is($notCollectedRepository))
        ->toBeTrue();
});

test('not collected leaves out a collected repository', function (): void {
    Repository::factory()->create(['missing_at' => null]);

    $notCollectedRepositories = Repository::query()
        ->notCollected()
        ->get();

    expect($notCollectedRepositories)
        ->toBeEmpty();
});

test('returned by run keeps a repository returned by the run', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesReturnedByRun = Repository::query()
        ->returnedByRun($repository->run_id)
        ->get();

    expect($repositoriesReturnedByRun->sole()->is($repository))
        ->toBeTrue();
});

test('returned by run leaves out a repository returned by another run', function (): void {
    Repository::factory()->create();

    $repositoriesReturnedByRun = Repository::query()
        ->returnedByRun(Uuid::generate())
        ->get();

    expect($repositoriesReturnedByRun)
        ->toBeEmpty();
});

test('not returned by run keeps a repository returned by another run', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesNotReturnedByRun = Repository::query()
        ->notReturnedByRun(Uuid::generate())
        ->get();

    expect($repositoriesNotReturnedByRun->sole()->is($repository))
        ->toBeTrue();
});

test('not returned by run leaves out a repository returned by the run', function (): void {
    $repository = Repository::factory()->create();

    $repositoriesNotReturnedByRun = Repository::query()
        ->notReturnedByRun($repository->run_id)
        ->get();

    expect($repositoriesNotReturnedByRun)
        ->toBeEmpty();
});
