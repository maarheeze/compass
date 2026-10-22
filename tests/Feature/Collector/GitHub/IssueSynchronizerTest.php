<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubRepositoryCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\GitHubIssueFactory;
use Maarheeze\Compass\Tests\Fixtures\GitHubRepositoryFactory;

test('store issues stores the issues of the repositories', function (): void {
    $repository = Repository::factory()->create();
    $gitHubIssue = GitHubIssueFactory::make();
    $collectionRunId = CollectionRunId::generate();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(
        new GitHubRepositoryCollection([GitHubRepositoryFactory::forRepositoryWithIssue($repository, $gitHubIssue)]),
        $collectionRunId,
    );

    $this->assertDatabaseHas(Issue::class, [
        'repository_id' => $repository->id,
        'github_id' => $gitHubIssue->gitHubId,
        'number' => $gitHubIssue->number,
        'title' => $gitHubIssue->title,
        'url' => $gitHubIssue->url,
        'author_login' => $gitHubIssue->authorLogin,
        'collection_run_id' => $collectionRunId,
    ]);
});

test('store issues stores the assignees of the issues', function (): void {
    $repository = Repository::factory()->create();
    $gitHubIssue = GitHubIssueFactory::make();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(
        new GitHubRepositoryCollection([GitHubRepositoryFactory::forRepositoryWithIssue($repository, $gitHubIssue)]),
        CollectionRunId::generate(),
    );

    $this->assertDatabaseHas(IssueAssignee::class, [
        'issue_id' => Issue::query()->where('github_id', $gitHubIssue->gitHubId)->sole()->id,
        'login' => $gitHubIssue->assigneeLogins[0],
    ]);
});

test('store issues removes the assignees collected before', function (): void {
    $repository = Repository::factory()->create();
    $gitHubIssue = GitHubIssueFactory::make();
    $issue = Issue::factory()->create([
        'repository_id' => $repository->id,
        'github_id' => $gitHubIssue->gitHubId,
    ]);
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(
        new GitHubRepositoryCollection([GitHubRepositoryFactory::forRepositoryWithIssue($repository, $gitHubIssue)]),
        CollectionRunId::generate(),
    );

    $this->assertModelMissing($assignee);
});

test('delete uncollected deletes an issue of another run', function (): void {
    $issue = Issue::factory()->create();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->deleteUncollected(CollectionRunId::generate());

    $this->assertModelMissing($issue);
});

test('delete uncollected keeps an issue of the run', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $issue = Issue::factory()->create(['collection_run_id' => $collectionRunId]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->deleteUncollected($collectionRunId);

    $this->assertModelExists($issue);
});
