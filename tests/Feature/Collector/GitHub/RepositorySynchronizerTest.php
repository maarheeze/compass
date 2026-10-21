<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\GitHubRepository;
use Maarheeze\Compass\Collector\GitHub\RepositorySynchronizer;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\GitHubRepositoryFactory;

test('store repositories creates a repository for a new github repository', function (): void {
    $gitHubRepository = GitHubRepositoryFactory::make();
    $collectionRunId = CollectionRunId::generate();

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new GitHubRepositoryCollection([$gitHubRepository]), $collectionRunId);

    $this->assertDatabaseHas(Repository::class, [
        'github_id' => $gitHubRepository->gitHubId,
        'name' => $gitHubRepository->name,
        'url' => $gitHubRepository->url,
        'default_branch' => $gitHubRepository->defaultBranch,
        'last_commit_sha' => $gitHubRepository->lastCommitSha,
        'visibility' => $gitHubRepository->visibility,
        'composer_json_oid' => $gitHubRepository->composerJsonOid,
        'composer_lock_oid' => $gitHubRepository->composerLockOid,
        'collection_run_id' => $collectionRunId,
    ]);
});

test('store repositories updates the default branch of a known repository', function (): void {
    $repository = Repository::factory()->create(['default_branch' => fake()->unique()->word()]);
    $gitHubRepository = GitHubRepositoryFactory::forRepository($repository);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new GitHubRepositoryCollection([$gitHubRepository]), CollectionRunId::generate());

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'default_branch' => $gitHubRepository->defaultBranch,
    ]);
});

test('store repositories updates the last commit sha of a known repository', function (): void {
    $repository = Repository::factory()->create();
    $gitHubRepository = GitHubRepositoryFactory::forRepository($repository);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new GitHubRepositoryCollection([$gitHubRepository]), CollectionRunId::generate());

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'last_commit_sha' => $gitHubRepository->lastCommitSha,
    ]);
});

test('store repositories updates the name of a known repository', function (): void {
    $repository = Repository::factory()->create(['name' => fake()->unique()->slug()]);
    $gitHubRepository = new GitHubRepository(
        $repository->github_id,
        fake()->unique()->slug(),
        fake()->url(),
        fake()->word(),
        fake()->sha1(),
        fake()->randomElement(RepositoryVisibility::cases()),
        fake()->sha1(),
        fake()->sha1(),
    );

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new GitHubRepositoryCollection([$gitHubRepository]), CollectionRunId::generate());

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'name' => $gitHubRepository->name,
    ]);
});

test('delete uncollected deletes a repository of another run', function (): void {
    $repository = Repository::factory()->create();

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->deleteUncollected(CollectionRunId::generate());

    $this->assertModelMissing($repository);
});

test('delete uncollected keeps a repository of the run', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $repository = Repository::factory()->create(['collection_run_id' => $collectionRunId]);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->deleteUncollected($collectionRunId);

    $this->assertModelExists($repository);
});
