<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\CollectedIssueCollection;
use Maarheeze\Compass\Collector\GitHub\IssueSynchronizer;
use Maarheeze\Compass\Collector\Issues\CollectedIssue;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Tests\Fixtures\CollectedIssueFactory;
use Maarheeze\Uuid\Uuid;

test('store issues stores an issue in its repository', function (): void {
    $repository = Repository::factory()->create();
    $collectedIssue = CollectedIssueFactory::make();
    $collectedIssueOfRepository = new CollectedIssue(
        $collectedIssue->externalId,
        $repository->name,
        $collectedIssue->number,
        $collectedIssue->title,
        $collectedIssue->url,
        $collectedIssue->authorLogin,
        $collectedIssue->openedAt,
        $collectedIssue->assigneeLogins,
    );
    $runId = Uuid::generate();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new CollectedIssueCollection([$collectedIssueOfRepository]), $runId);

    $this->assertDatabaseHas(Issue::class, [
        'repository_id' => $repository->id,
        'github_id' => $collectedIssue->externalId,
        'number' => $collectedIssue->number,
        'title' => $collectedIssue->title,
        'url' => $collectedIssue->url,
        'author_login' => $collectedIssue->authorLogin,
        'run_id' => $runId,
    ]);
});

test('store issues skips an issue of an unknown repository', function (): void {
    $collectedIssue = CollectedIssueFactory::make();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new CollectedIssueCollection([$collectedIssue]), Uuid::generate());

    $this->assertDatabaseMissing(Issue::class, ['github_id' => $collectedIssue->externalId]);
});

test('store issues stores the assignees of the issues', function (): void {
    $repository = Repository::factory()->create();
    $collectedIssue = CollectedIssueFactory::make();
    $collectedIssueOfRepository = new CollectedIssue(
        $collectedIssue->externalId,
        $repository->name,
        $collectedIssue->number,
        $collectedIssue->title,
        $collectedIssue->url,
        $collectedIssue->authorLogin,
        $collectedIssue->openedAt,
        $collectedIssue->assigneeLogins,
    );

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new CollectedIssueCollection([$collectedIssueOfRepository]), Uuid::generate());

    $this->assertDatabaseHas(IssueAssignee::class, [
        'issue_id' => Issue::query()->where('github_id', $collectedIssue->externalId)->sole()->id,
        'login' => $collectedIssue->assigneeLogins[0],
    ]);
});

test('store issues removes the assignees collected before', function (): void {
    $repository = Repository::factory()->create();
    $collectedIssue = CollectedIssueFactory::make();
    $collectedIssueOfRepository = new CollectedIssue(
        $collectedIssue->externalId,
        $repository->name,
        $collectedIssue->number,
        $collectedIssue->title,
        $collectedIssue->url,
        $collectedIssue->authorLogin,
        $collectedIssue->openedAt,
        $collectedIssue->assigneeLogins,
    );
    $issue = Issue::factory()->create([
        'repository_id' => $repository->id,
        'github_id' => $collectedIssue->externalId,
    ]);
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new CollectedIssueCollection([$collectedIssueOfRepository]), Uuid::generate());

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
