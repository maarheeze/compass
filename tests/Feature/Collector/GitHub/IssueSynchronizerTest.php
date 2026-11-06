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

test('store issues stores an issue of the source', function (): void {
    $source = fake()->slug();
    $collectedIssue = CollectedIssueFactory::make();
    $runId = Uuid::generate();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new CollectedIssueCollection([$collectedIssue]), $source, $runId);

    $this->assertDatabaseHas(Issue::class, [
        'source' => $source,
        'external_id' => $collectedIssue->externalId,
        'repository_lookup_key' => $collectedIssue->repositoryLookupKey,
        'number' => $collectedIssue->number,
        'title' => $collectedIssue->title,
        'url' => $collectedIssue->url,
        'author_login' => $collectedIssue->authorLogin,
        'run_id' => $runId,
    ]);
});

test('store issues links an issue to its repository of the source', function (): void {
    $repository = Repository::factory()->create();
    $collectedIssue = CollectedIssueFactory::make();
    $collectedIssueOfRepository = new CollectedIssue(
        $collectedIssue->externalId,
        $repository->full_name,
        $collectedIssue->number,
        $collectedIssue->title,
        $collectedIssue->url,
        $collectedIssue->authorLogin,
        $collectedIssue->openedAt,
        $collectedIssue->assignees,
    );

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(
        new CollectedIssueCollection([$collectedIssueOfRepository]),
        $repository->source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(Issue::class, [
        'source' => $repository->source,
        'external_id' => $collectedIssue->externalId,
        'repository_id' => $repository->id,
    ]);
});

test('store issues leaves an issue unlinked to a repository of another source', function (): void {
    $source = fake()->unique()->slug();
    $repository = Repository::factory()->create(['source' => fake()->unique()->slug()]);
    $collectedIssue = CollectedIssueFactory::make();
    $collectedIssueOfRepository = new CollectedIssue(
        $collectedIssue->externalId,
        $repository->full_name,
        $collectedIssue->number,
        $collectedIssue->title,
        $collectedIssue->url,
        $collectedIssue->authorLogin,
        $collectedIssue->openedAt,
        $collectedIssue->assignees,
    );

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(
        new CollectedIssueCollection([$collectedIssueOfRepository]),
        $source,
        Uuid::generate(),
    );

    $this->assertDatabaseHas(Issue::class, [
        'source' => $source,
        'external_id' => $collectedIssue->externalId,
        'repository_id' => null,
    ]);
});

test('store issues stores an issue of a repository not stored without a repository', function (): void {
    $source = fake()->slug();
    $collectedIssue = CollectedIssueFactory::make();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new CollectedIssueCollection([$collectedIssue]), $source, Uuid::generate());

    $this->assertDatabaseHas(Issue::class, [
        'source' => $source,
        'external_id' => $collectedIssue->externalId,
        'repository_id' => null,
    ]);
});

test('store issues stores the assignees of the issues', function (): void {
    $source = fake()->slug();
    $collectedIssue = CollectedIssueFactory::make();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(new CollectedIssueCollection([$collectedIssue]), $source, Uuid::generate());

    $this->assertDatabaseHas(IssueAssignee::class, [
        'issue_id' => Issue::query()
            ->ofSource($source)
            ->where('external_id', $collectedIssue->externalId)
            ->sole()
            ->id,
        'login' => $collectedIssue->assignees->sole()->login,
        'avatar_url' => $collectedIssue->assignees->sole()->avatarUrl,
    ]);
});

test('store issues removes the assignees collected before', function (): void {
    $collectedIssue = CollectedIssueFactory::make();
    $issue = Issue::factory()->create(['external_id' => $collectedIssue->externalId]);
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(
        new CollectedIssueCollection([$collectedIssue]),
        $issue->source,
        Uuid::generate(),
    );

    $this->assertModelMissing($assignee);
});

test('store issues keeps the assignees of an issue of another source', function (): void {
    $collectedIssue = CollectedIssueFactory::make();
    $issue = Issue::factory()->create([
        'source' => fake()->unique()->slug(),
        'external_id' => $collectedIssue->externalId,
    ]);
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->storeIssues(
        new CollectedIssueCollection([$collectedIssue]),
        fake()->unique()->slug(),
        Uuid::generate(),
    );

    $this->assertModelExists($assignee);
});

test('delete uncollected deletes an issue of the source of another run', function (): void {
    $issue = Issue::factory()->create();

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->deleteUncollected($issue->source, Uuid::generate());

    $this->assertModelMissing($issue);
});

test('delete uncollected keeps an issue of the run', function (): void {
    $runId = Uuid::generate();
    $issue = Issue::factory()->create(['run_id' => $runId]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->deleteUncollected($issue->source, $runId);

    $this->assertModelExists($issue);
});

test('delete uncollected keeps an issue of another source', function (): void {
    $issue = Issue::factory()->create(['source' => fake()->unique()->slug()]);

    $issueSynchronizer = $this->app->make(IssueSynchronizer::class);
    $issueSynchronizer->deleteUncollected(fake()->unique()->slug(), Uuid::generate());

    $this->assertModelExists($issue);
});
