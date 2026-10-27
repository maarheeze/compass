<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\GitHubIssueCollection;
use Maarheeze\Compass\Collector\GitHub\GitHubIssue;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\GitHubIssueFactory;
use Maarheeze\Uuid\Uuid;

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
    $runId = Uuid::generate();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssueOfRepository]), $runId);

    $this->assertDatabaseHas(Issue::class, [
        'repository_id' => $repository->id,
        'github_id' => $gitHubIssue->gitHubId,
        'number' => $gitHubIssue->number,
        'title' => $gitHubIssue->title,
        'url' => $gitHubIssue->url,
        'author_login' => $gitHubIssue->authorLogin,
        'run_id' => $runId,
    ]);
});

test('store issues skips an issue of an unknown repository', function (): void {
    $gitHubIssue = GitHubIssueFactory::make();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssue]), Uuid::generate());

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
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssueOfRepository]), Uuid::generate());

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
    $issueSynchronizer->storeIssues(new GitHubIssueCollection([$gitHubIssueOfRepository]), Uuid::generate());

    $this->assertModelMissing($assignee);
});

test('delete uncollected deletes an issue of another run', function (): void {
    $issue = Issue::factory()->create();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->deleteUncollected(Uuid::generate());

    $this->assertModelMissing($issue);
});

test('delete uncollected keeps an issue of the run', function (): void {
    $runId = Uuid::generate();
    $issue = Issue::factory()->create(['run_id' => $runId]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->deleteUncollected($runId);

    $this->assertModelExists($issue);
});
