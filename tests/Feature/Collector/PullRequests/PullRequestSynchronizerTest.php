<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\PullRequests\CollectedPullRequest;
use Maarheeze\Compass\Collector\PullRequests\PullRequestSynchronizer;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\CollectedPullRequestFactory;
use Maarheeze\Uuid\Uuid;
use Psr\Clock\ClockInterface;
use Symfony\Component\Clock\MockClock;

test('store pull requests stores a pull request of the source', function (): void {
    $source = fake()->slug();
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $runId = Uuid::generate();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequest]),
        $source,
        $runId,
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'source' => $source,
        'external_id' => $collectedPullRequest->externalId,
        'repository_lookup_key' => $collectedPullRequest->repositoryLookupKey,
        'number' => $collectedPullRequest->number,
        'title' => $collectedPullRequest->title,
        'url' => $collectedPullRequest->url,
        'author_login' => $collectedPullRequest->authorLogin,
        'is_draft' => $collectedPullRequest->isDraft,
        'run_id' => $runId,
    ]);
});

test('store pull requests stamps a pull request with the moment it was collected', function (): void {
    $now = CarbonImmutable::parse(fake()->iso8601());
    $this->app->instance(ClockInterface::class, new MockClock($now));
    $source = fake()->slug();
    $collectedPullRequest = CollectedPullRequestFactory::make();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequest]),
        $source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'source' => $source,
        'external_id' => $collectedPullRequest->externalId,
        'collected_at' => $now,
    ]);
});

test('store pull requests links a pull request to its repository of the source', function (): void {
    $repository = Repository::factory()->create();
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $collectedPullRequestOfRepository = new CollectedPullRequest(
        $collectedPullRequest->externalId,
        $repository->full_name,
        $collectedPullRequest->number,
        $collectedPullRequest->title,
        $collectedPullRequest->url,
        $collectedPullRequest->authorLogin,
        $collectedPullRequest->isDraft,
        $collectedPullRequest->openedAt,
        $collectedPullRequest->assignees,
    );

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequestOfRepository]),
        $repository->source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'source' => $repository->source,
        'external_id' => $collectedPullRequest->externalId,
        'repository_id' => $repository->id,
    ]);
});

test('store pull requests leaves a pull request unlinked to a repository of another source', function (): void {
    $source = fake()->unique()->slug();
    $repository = Repository::factory()->create(['source' => fake()->unique()->slug()]);
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $collectedPullRequestOfRepository = new CollectedPullRequest(
        $collectedPullRequest->externalId,
        $repository->full_name,
        $collectedPullRequest->number,
        $collectedPullRequest->title,
        $collectedPullRequest->url,
        $collectedPullRequest->authorLogin,
        $collectedPullRequest->isDraft,
        $collectedPullRequest->openedAt,
        $collectedPullRequest->assignees,
    );

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequestOfRepository]),
        $source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'source' => $source,
        'external_id' => $collectedPullRequest->externalId,
        'repository_id' => null,
    ]);
});

test('store pull requests stores a pull request of a repository not stored without a repository', function (): void {
    $source = fake()->slug();
    $collectedPullRequest = CollectedPullRequestFactory::make();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequest]),
        $source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'source' => $source,
        'external_id' => $collectedPullRequest->externalId,
        'repository_id' => null,
    ]);
});

test('store pull requests links an unlinked pull request to its repository stored since', function (): void {
    $repository = Repository::factory()->create();
    $pullRequest = PullRequest::factory()->create([
        'repository_id' => null,
        'source' => $repository->source,
        'repository_lookup_key' => $repository->full_name,
    ]);
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $collectedPullRequestOfRepository = new CollectedPullRequest(
        $pullRequest->external_id,
        $repository->full_name,
        $collectedPullRequest->number,
        $collectedPullRequest->title,
        $collectedPullRequest->url,
        $collectedPullRequest->authorLogin,
        $collectedPullRequest->isDraft,
        $collectedPullRequest->openedAt,
        $collectedPullRequest->assignees,
    );

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequestOfRepository]),
        $repository->source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'id' => $pullRequest->id,
        'repository_id' => $repository->id,
    ]);
});

test('store pull requests stores the assignees of the pull requests', function (): void {
    $source = fake()->slug();
    $collectedPullRequest = CollectedPullRequestFactory::make();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequest]),
        $source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(PullRequestAssignee::class, [
        'pull_request_id' => PullRequest::query()
            ->ofSource($source)
            ->where('external_id', $collectedPullRequest->externalId)
            ->sole()
            ->id,
        'login' => $collectedPullRequest->assignees->sole()->login,
        'avatar_url' => $collectedPullRequest->assignees->sole()->avatarUrl,
    ]);
});

test('store pull requests removes the assignees collected before', function (): void {
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $pullRequest = PullRequest::factory()->create(['external_id' => $collectedPullRequest->externalId]);
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequest]),
        $pullRequest->source,
        Uuid::generate(),
    );

    $this->assertModelMissing($assignee);
});

test('store pull requests keeps the assignees of a pull request of another source', function (): void {
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $pullRequest = PullRequest::factory()->create([
        'source' => fake()->unique()->slug(),
        'external_id' => $collectedPullRequest->externalId,
    ]);
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequest]),
        fake()->unique()->slug(),
        Uuid::generate(),
    );

    $this->assertModelExists($assignee);
});

test('delete uncollected deletes a pull request of the source of another run', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->deleteUncollected($pullRequest->source, Uuid::generate());

    $this->assertModelMissing($pullRequest);
});

test('delete uncollected keeps a pull request of the run', function (): void {
    $runId = Uuid::generate();
    $pullRequest = PullRequest::factory()->create(['run_id' => $runId]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->deleteUncollected($pullRequest->source, $runId);

    $this->assertModelExists($pullRequest);
});

test('delete uncollected keeps a pull request of another source', function (): void {
    $pullRequest = PullRequest::factory()->create(['source' => fake()->unique()->slug()]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->deleteUncollected(fake()->unique()->slug(), Uuid::generate());

    $this->assertModelExists($pullRequest);
});
