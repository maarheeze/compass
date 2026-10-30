<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Events\RepositoryMissingIgnored;
use Maarheeze\Compass\Collector\GitHub\MissingRepositoryResolver;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Repository;

test('can ignore is true for a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $canIgnore = $missingRepositoryResolver->canIgnore($repository);

    expect($canIgnore)
        ->toBeTrue();
});

test('can ignore is false for a collected repository', function (): void {
    $repository = Repository::factory()->create();

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $canIgnore = $missingRepositoryResolver->canIgnore($repository);

    expect($canIgnore)
        ->toBeFalse();
});

test('can ignore is false for a repository that is already ignored', function (): void {
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $canIgnore = $missingRepositoryResolver->canIgnore($repository);

    expect($canIgnore)
        ->toBeFalse();
});

test('delete deletes a missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->delete($repository);

    $this->assertModelMissing($repository);
});

test('delete deletes the alerts of the missing repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $alert = Alert::factory()
        ->missingRepository()
        ->for($repository, 'subject')
        ->create();

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->delete($repository);

    $this->assertModelMissing($alert);
});

test('delete keeps the alerts of another repository', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);
    $otherAlert = Alert::factory()
        ->missingRepository()
        ->create();

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->delete($repository);

    $this->assertModelExists($otherAlert);
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

test('ignore raises repository missing ignored with the repository', function (): void {
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $missingRepositoryResolver = $this->app->make(MissingRepositoryResolver::class);
    $missingRepositoryResolver->ignore($repository);

    $eventFake->assertDispatched(RepositoryMissingIgnored::class, static function (RepositoryMissingIgnored $repositoryMissingIgnored) use ($repository): bool {
        return $repositoryMissingIgnored->repository->is($repository);
    });
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
