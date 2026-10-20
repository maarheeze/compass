<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHubRepository;
use Maarheeze\Compass\Collector\RepositorySynchronizer;
use Maarheeze\Compass\Models\Repository;

test('store repositories creates a repository for a new github repository', function (): void {
    $gitHubRepository = new GitHubRepository(fake()->slug(), fake()->word());
    $collectionRunId = CollectionRunId::generate();

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new GitHubRepositoryCollection([$gitHubRepository]), $collectionRunId);

    $this->assertDatabaseHas(Repository::class, [
        'name' => $gitHubRepository->name,
        'default_branch' => $gitHubRepository->defaultBranch,
        'collection_run_id' => $collectionRunId,
        'is_active' => true,
    ]);
});

test('store repositories updates the default branch of a known repository', function (): void {
    $repository = Repository::factory()->create(['default_branch' => fake()->unique()->word()]);
    $gitHubRepository = new GitHubRepository($repository->name, fake()->unique()->word());

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new GitHubRepositoryCollection([$gitHubRepository]), CollectionRunId::generate());

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'default_branch' => $gitHubRepository->defaultBranch,
    ]);
});

test('store repositories reactivates an inactive repository', function (): void {
    $repository = Repository::factory()->create(['is_active' => false]);
    $gitHubRepository = new GitHubRepository($repository->name, fake()->word());

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new GitHubRepositoryCollection([$gitHubRepository]), CollectionRunId::generate());

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'is_active' => true,
    ]);
});

test('mark repositories not in collection run inactive marks a repository of another run inactive', function (): void {
    $repository = Repository::factory()->create(['is_active' => true]);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->updateInactive(CollectionRunId::generate());

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'is_active' => false,
    ]);
});

test('mark repositories not in collection run inactive keeps a repository of the run active', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create([
        'collection_run_id' => $collectionRunId,
        'is_active' => true,
    ]);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->updateInactive($collectionRunId);

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'is_active' => true,
    ]);
});
