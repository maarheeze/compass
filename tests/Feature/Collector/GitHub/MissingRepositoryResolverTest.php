<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Maarheeze\Compass\Collector\GitHub\MissingRepositoryResolver;
use Maarheeze\Compass\Models\Repository;

test('delete deletes a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->delete($repository);

    $this->assertModelMissing($repository);
});

test('delete refuses a collected repository', function (): void {
    $repository = Repository::factory()->create();

    $this->expectException(ModelNotFoundException::class);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->delete($repository);
});

test('ignore marks a missing repository ignored', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->ignore($repository);

    expect($repository->refresh()->missing_ignored_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('ignore refuses a collected repository', function (): void {
    $repository = Repository::factory()->create();

    $this->expectException(ModelNotFoundException::class);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->ignore($repository);
});

test('ignore refuses a repository that is already ignored', function (): void {
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $this->expectException(ModelNotFoundException::class);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->ignore($repository);
});
