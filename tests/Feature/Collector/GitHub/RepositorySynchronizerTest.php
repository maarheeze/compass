<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedRepositoryCollection;
use Maarheeze\Compass\Collector\GitHub\RepositorySynchronizer;
use Maarheeze\Compass\Collector\Repositories\CollectedRepository;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\CollectedRepositoryFactory;
use Maarheeze\Uuid\Uuid;

test('store repositories creates a repository for a new collected repository', function (): void {
    $collectedRepository = CollectedRepositoryFactory::make();
    $runId = Uuid::generate();

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(new CollectedRepositoryCollection([$collectedRepository]), $runId);

    $this->assertDatabaseHas(Repository::class, [
        'github_id' => $collectedRepository->externalId,
        'name' => $collectedRepository->name,
        'url' => $collectedRepository->url,
        'default_branch' => $collectedRepository->defaultBranch,
        'last_commit_sha' => $collectedRepository->lastCommitSha,
        'last_commit_committed_at' => $collectedRepository->lastCommitCommittedAt,
        'visibility' => $collectedRepository->visibility,
        'composer_json_oid' => $collectedRepository->composerJsonVersion,
        'composer_lock_oid' => $collectedRepository->composerLockVersion,
        'package_json_oid' => $collectedRepository->packageJsonVersion,
        'dependabot_yml_oid' => $collectedRepository->dependabotYmlVersion,
        'run_id' => $runId,
    ]);
});

test('store repositories updates the default branch of a known repository', function (): void {
    $repository = Repository::factory()->create(['default_branch' => fake()->unique()->word()]);
    $collectedRepository = CollectedRepositoryFactory::forRepository($repository);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(
        new CollectedRepositoryCollection([$collectedRepository]),
        Uuid::generate(),
    );

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'default_branch' => $collectedRepository->defaultBranch,
    ]);
});

test('store repositories updates the last commit sha of a known repository', function (): void {
    $repository = Repository::factory()->create();
    $collectedRepository = CollectedRepositoryFactory::forRepository($repository);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(
        new CollectedRepositoryCollection([$collectedRepository]),
        Uuid::generate(),
    );

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'last_commit_sha' => $collectedRepository->lastCommitSha,
    ]);
});

test('store repositories updates the name of a known repository', function (): void {
    $repository = Repository::factory()->create(['name' => fake()->unique()->slug()]);
    $collectedRepository = new CollectedRepository(
        $repository->github_id,
        fake()->unique()->slug(),
        fake()->url(),
        fake()->word(),
        fake()->sha1(),
        CarbonImmutable::parse(fake()->iso8601()),
        fake()->randomElement(RepositoryVisibility::cases()),
        fake()->sha1(),
        fake()->sha1(),
        fake()->sha1(),
        fake()->sha1(),
    );

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(
        new CollectedRepositoryCollection([$collectedRepository]),
        Uuid::generate(),
    );

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'name' => $collectedRepository->name,
    ]);
});

test('store repositories clears the missing marks of a known repository', function (): void {
    $repository = Repository::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);
    $collectedRepository = CollectedRepositoryFactory::forRepository($repository);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->storeRepositories(
        new CollectedRepositoryCollection([$collectedRepository]),
        Uuid::generate(),
    );

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);
});

test('mark uncollected missing marks a repository of another run missing', function (): void {
    $repository = Repository::factory()->create();

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->markUncollectedMissing(Uuid::generate());

    expect($repository->refresh()->missing_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('mark uncollected missing keeps the moment a repository went missing', function (): void {
    $missingAt = fake()->dateTime();
    $repository = Repository::factory()->create(['missing_at' => $missingAt]);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->markUncollectedMissing(Uuid::generate());

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'missing_at' => $missingAt,
    ]);
});

test('mark uncollected missing leaves a repository of the run unmarked', function (): void {
    $runId = Uuid::generate();
    $repository = Repository::factory()->create(['run_id' => $runId]);

    $repositorySynchronizer = $this->app->make(RepositorySynchronizer::class);
    $repositorySynchronizer->markUncollectedMissing($runId);

    $this->assertDatabaseHas(Repository::class, [
        'id' => $repository->id,
        'missing_at' => null,
    ]);
});
