<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubPullRequestCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\GitHubPullRequest;
use Maarheeze\Compass\Collector\GitHub\PullRequestSynchronizer;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\GitHubPullRequestFactory;

test('store pull requests stores a pull request in its repository', function (): void {
    $repository = Repository::factory()->create();
    $gitHubPullRequest = GitHubPullRequestFactory::make();
    $gitHubPullRequestOfRepository = new GitHubPullRequest(
        $gitHubPullRequest->gitHubId,
        $repository->name,
        $gitHubPullRequest->number,
        $gitHubPullRequest->title,
        $gitHubPullRequest->url,
        $gitHubPullRequest->authorLogin,
        $gitHubPullRequest->isDraft,
        $gitHubPullRequest->openedAt,
        $gitHubPullRequest->assigneeLogins,
    );
    $collectionRunId = CollectionRunId::generate();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new GitHubPullRequestCollection([$gitHubPullRequestOfRepository]),
        $collectionRunId,
    );

    $this->assertDatabaseHas(PullRequest::class, [
        'repository_id' => $repository->id,
        'github_id' => $gitHubPullRequest->gitHubId,
        'number' => $gitHubPullRequest->number,
        'title' => $gitHubPullRequest->title,
        'url' => $gitHubPullRequest->url,
        'author_login' => $gitHubPullRequest->authorLogin,
        'is_draft' => $gitHubPullRequest->isDraft,
        'collection_run_id' => $collectionRunId,
    ]);
});

test('store pull requests skips a pull request of an unknown repository', function (): void {
    $gitHubPullRequest = GitHubPullRequestFactory::make();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new GitHubPullRequestCollection([$gitHubPullRequest]),
        CollectionRunId::generate(),
    );

    $this->assertDatabaseMissing(PullRequest::class, ['github_id' => $gitHubPullRequest->gitHubId]);
});

test('store pull requests stores the assignees of the pull requests', function (): void {
    $repository = Repository::factory()->create();
    $gitHubPullRequest = GitHubPullRequestFactory::make();
    $gitHubPullRequestOfRepository = new GitHubPullRequest(
        $gitHubPullRequest->gitHubId,
        $repository->name,
        $gitHubPullRequest->number,
        $gitHubPullRequest->title,
        $gitHubPullRequest->url,
        $gitHubPullRequest->authorLogin,
        $gitHubPullRequest->isDraft,
        $gitHubPullRequest->openedAt,
        $gitHubPullRequest->assigneeLogins,
    );

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new GitHubPullRequestCollection([$gitHubPullRequestOfRepository]),
        CollectionRunId::generate(),
    );

    $this->assertDatabaseHas(PullRequestAssignee::class, [
        'pull_request_id' => PullRequest::query()->where('github_id', $gitHubPullRequest->gitHubId)->sole()->id,
        'login' => $gitHubPullRequest->assigneeLogins[0],
    ]);
});

test('store pull requests removes the assignees collected before', function (): void {
    $repository = Repository::factory()->create();
    $gitHubPullRequest = GitHubPullRequestFactory::make();
    $gitHubPullRequestOfRepository = new GitHubPullRequest(
        $gitHubPullRequest->gitHubId,
        $repository->name,
        $gitHubPullRequest->number,
        $gitHubPullRequest->title,
        $gitHubPullRequest->url,
        $gitHubPullRequest->authorLogin,
        $gitHubPullRequest->isDraft,
        $gitHubPullRequest->openedAt,
        $gitHubPullRequest->assigneeLogins,
    );
    $pullRequest = PullRequest::factory()->create([
        'repository_id' => $repository->id,
        'github_id' => $gitHubPullRequest->gitHubId,
    ]);
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->storePullRequests(
        new GitHubPullRequestCollection([$gitHubPullRequestOfRepository]),
        CollectionRunId::generate(),
    );

    $this->assertModelMissing($assignee);
});

test('delete uncollected deletes a pull request of another run', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->deleteUncollected(CollectionRunId::generate());

    $this->assertModelMissing($pullRequest);
});

test('delete uncollected keeps a pull request of the run', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $pullRequest = PullRequest::factory()->create(['collection_run_id' => $collectionRunId]);

    $pullRequestSynchronizer = $this->app->make(PullRequestSynchronizer::class);
    $pullRequestSynchronizer->deleteUncollected($collectionRunId);

    $this->assertModelExists($pullRequest);
});
