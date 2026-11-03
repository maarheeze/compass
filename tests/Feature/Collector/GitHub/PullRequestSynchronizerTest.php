<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedPullRequestCollection;
use Maarheeze\Compass\Collector\GitHub\PullRequestSynchronizer;
use Maarheeze\Compass\Collector\PullRequests\CollectedPullRequest;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\CollectedPullRequestFactory;
use Maarheeze\Uuid\Uuid;

test('store pull requests stores a pull request in its repository', function (): void {
    $repository = Repository::factory()->create();
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $collectedPullRequestOfRepository = new CollectedPullRequest(
        $collectedPullRequest->externalId,
        $repository->name,
        $collectedPullRequest->number,
        $collectedPullRequest->title,
        $collectedPullRequest->url,
        $collectedPullRequest->authorLogin,
        $collectedPullRequest->isDraft,
        $collectedPullRequest->openedAt,
        $collectedPullRequest->assigneeLogins,
    );
    $runId = Uuid::generate();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequestOfRepository]),
        $runId,
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'repository_id' => $repository->id,
        'github_id' => $collectedPullRequest->externalId,
        'number' => $collectedPullRequest->number,
        'title' => $collectedPullRequest->title,
        'url' => $collectedPullRequest->url,
        'author_login' => $collectedPullRequest->authorLogin,
        'is_draft' => $collectedPullRequest->isDraft,
        'run_id' => $runId,
    ]);
});

test('store pull requests skips a pull request of an unknown repository', function (): void {
    $collectedPullRequest = CollectedPullRequestFactory::make();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequest]),
        Uuid::generate(),
    );

    $this->assertDatabaseMissing(PullRequest::class, ['github_id' => $collectedPullRequest->externalId]);
});

test('store pull requests stores the assignees of the pull requests', function (): void {
    $repository = Repository::factory()->create();
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $collectedPullRequestOfRepository = new CollectedPullRequest(
        $collectedPullRequest->externalId,
        $repository->name,
        $collectedPullRequest->number,
        $collectedPullRequest->title,
        $collectedPullRequest->url,
        $collectedPullRequest->authorLogin,
        $collectedPullRequest->isDraft,
        $collectedPullRequest->openedAt,
        $collectedPullRequest->assigneeLogins,
    );

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequestOfRepository]),
        Uuid::generate(),
    );

    $this->assertDatabaseHas(PullRequestAssignee::class, [
        'pull_request_id' => PullRequest::query()->where('github_id', $collectedPullRequest->externalId)->sole()->id,
        'login' => $collectedPullRequest->assigneeLogins[0],
    ]);
});

test('store pull requests removes the assignees collected before', function (): void {
    $repository = Repository::factory()->create();
    $collectedPullRequest = CollectedPullRequestFactory::make();
    $collectedPullRequestOfRepository = new CollectedPullRequest(
        $collectedPullRequest->externalId,
        $repository->name,
        $collectedPullRequest->number,
        $collectedPullRequest->title,
        $collectedPullRequest->url,
        $collectedPullRequest->authorLogin,
        $collectedPullRequest->isDraft,
        $collectedPullRequest->openedAt,
        $collectedPullRequest->assigneeLogins,
    );
    $pullRequest = PullRequest::factory()->create([
        'repository_id' => $repository->id,
        'github_id' => $collectedPullRequest->externalId,
    ]);
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new CollectedPullRequestCollection([$collectedPullRequestOfRepository]),
        Uuid::generate(),
    );

    $this->assertModelMissing($assignee);
});

test('delete uncollected deletes a pull request of another run', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->deleteUncollected(Uuid::generate());

    $this->assertModelMissing($pullRequest);
});

test('delete uncollected keeps a pull request of the run', function (): void {
    $runId = Uuid::generate();
    $pullRequest = PullRequest::factory()->create(['run_id' => $runId]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->deleteUncollected($runId);

    $this->assertModelExists($pullRequest);
});
