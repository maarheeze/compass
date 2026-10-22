<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubIssueCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\GitHub\GitHubIssue;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\GitHubIssueFactory;

test('store issues stores an issue in its repository', function (): void {
    $repository = Repository::factory()->create();
    $gitHubIssue = GitHubIssueFactory::make();
    $gitHubIssueOfRepository = new GitHubIssue(
        $gitHubIssue->gitHubId,
        $repository->name,
        $gitHubIssue->number,
        $gitHubIssue->title,
        $gitHubIssue->url,
        $gitHubIssue->authorLogin,
        $gitHubIssue->openedAt,
        $gitHubIssue->assigneeLogins,
    );
    $collectionRunId = CollectionRunId::generate();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssueOfRepository]), $collectionRunId);

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

test('store issues skips an issue of an unknown repository', function (): void {
    $gitHubIssue = GitHubIssueFactory::make();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssue]), CollectionRunId::generate());

    $this->assertDatabaseMissing(Issue::class, ['github_id' => $gitHubIssue->gitHubId]);
});

test('store issues stores the assignees of the issues', function (): void {
    $repository = Repository::factory()->create();
    $gitHubIssue = GitHubIssueFactory::make();
    $gitHubIssueOfRepository = new GitHubIssue(
        $gitHubIssue->gitHubId,
        $repository->name,
        $gitHubIssue->number,
        $gitHubIssue->title,
        $gitHubIssue->url,
        $gitHubIssue->authorLogin,
        $gitHubIssue->openedAt,
        $gitHubIssue->assigneeLogins,
    );

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssueOfRepository]), CollectionRunId::generate());

    $this->assertDatabaseHas(IssueAssignee::class, [
        'issue_id' => Issue::query()->where('github_id', $gitHubIssue->gitHubId)->sole()->id,
        'login' => $gitHubIssue->assigneeLogins[0],
    ]);
});

test('store issues removes the assignees collected before', function (): void {
    $repository = Repository::factory()->create();
    $gitHubIssue = GitHubIssueFactory::make();
    $gitHubIssueOfRepository = new GitHubIssue(
        $gitHubIssue->gitHubId,
        $repository->name,
        $gitHubIssue->number,
        $gitHubIssue->title,
        $gitHubIssue->url,
        $gitHubIssue->authorLogin,
        $gitHubIssue->openedAt,
        $gitHubIssue->assigneeLogins,
    );
    $issue = Issue::factory()->create([
        'repository_id' => $repository->id,
        'github_id' => $gitHubIssue->gitHubId,
    ]);
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssueOfRepository]), CollectionRunId::generate());

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
