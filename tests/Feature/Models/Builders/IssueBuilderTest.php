<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Uuid\Uuid;

test('not returned by run keeps a issue returned by another run', function (): void {
    $issue = Issue::factory()->create();

    $issuesNotReturnedByRun = Issue::query()
        ->notReturnedByRun(Uuid::generate())
        ->get();

    expect($issuesNotReturnedByRun->sole()->is($issue))
        ->toBeTrue();
});

test('not returned by run leaves out a issue returned by the run', function (): void {
    $issue = Issue::factory()->create();

    $issuesNotReturnedByRun = Issue::query()
        ->notReturnedByRun($issue->run_id)
        ->get();

    expect($issuesNotReturnedByRun)
        ->toBeEmpty();
});

test('of external ids keeps a issue with one of the ids', function (): void {
    $issue = Issue::factory()->create();

    $issuesOfExternalIds = Issue::query()
        ->ofExternalIds(new Collection([$issue->external_id]))
        ->get();

    expect($issuesOfExternalIds->sole()->is($issue))
        ->toBeTrue();
});

test('of external ids leaves out a issue with another id', function (): void {
    Issue::factory()->create();

    $issuesOfExternalIds = Issue::query()
        ->ofExternalIds(new Collection([fake()->unique()->sha1()]))
        ->get();

    expect($issuesOfExternalIds)
        ->toBeEmpty();
});

test('latest opened orders the issues from the most recently opened', function (): void {
    $olderIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-2 years', '-1 year')]);
    $newerIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-1 year')]);

    $issuesLatestOpened = Issue::query()
        ->latestOpened()
        ->get();

    expect($issuesLatestOpened->modelKeys())
        ->toEqual([$newerIssue->id, $olderIssue->id]);
});

test('of source keeps a issue of the source', function (): void {
    $issueOfSource = Issue::factory()->create();

    $issuesOfSource = Issue::query()
        ->ofSource($issueOfSource->source)
        ->get();

    expect($issuesOfSource->sole()->is($issueOfSource))
        ->toBeTrue();
});

test('of source leaves out a issue of another source', function (): void {
    Issue::factory()->create(['source' => fake()->unique()->slug()]);

    $issuesOfSource = Issue::query()
        ->ofSource(fake()->unique()->slug())
        ->get();

    expect($issuesOfSource)
        ->toBeEmpty();
});

test('listed is the issues opened latest first', function (): void {
    $olderIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $newerIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-1 week')]);

    $listedIssues = Issue::query()->listed();

    expect($listedIssues->modelKeys())
        ->toEqual([$newerIssue->getKey(), $olderIssue->getKey()]);
});

test('assigned to keeps an issue assigned to the assignee', function (): void {
    $issue = Issue::factory()->create();
    $issueAssignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issuesAssignedTo = Issue::query()
        ->assignedTo(new Assignee($issue->source, $issueAssignee->login))
        ->get();

    expect($issuesAssignedTo->sole()->is($issue))
        ->toBeTrue();
});

test('assigned to leaves out an issue assigned to someone else', function (): void {
    $issue = Issue::factory()->create();
    IssueAssignee::factory()->create(['issue_id' => $issue->id, 'login' => fake()->unique()->userName()]);

    $issuesAssignedTo = Issue::query()
        ->assignedTo(new Assignee($issue->source, fake()->unique()->userName()))
        ->get();

    expect($issuesAssignedTo)
        ->toBeEmpty();
});

test('assigned to leaves out an issue assigned to the same login of another source', function (): void {
    $issue = Issue::factory()->create(['source' => fake()->unique()->slug()]);
    $issueAssignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issuesAssignedTo = Issue::query()
        ->assignedTo(new Assignee(fake()->unique()->slug(), $issueAssignee->login))
        ->get();

    expect($issuesAssignedTo)
        ->toBeEmpty();
});

test('unassigned keeps an issue without an assignee', function (): void {
    $unassignedIssue = Issue::factory()->create();

    $unassignedIssues = Issue::query()
        ->unassigned()
        ->get();

    expect($unassignedIssues->sole()->is($unassignedIssue))
        ->toBeTrue();
});

test('unassigned leaves out an issue with an assignee', function (): void {
    IssueAssignee::factory()->create();

    $unassignedIssues = Issue::query()
        ->unassigned()
        ->get();

    expect($unassignedIssues)
        ->toBeEmpty();
});

test('listed page is the issues opened latest first', function (): void {
    $olderIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $newerIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-1 week')]);

    $listedIssuesPage = Issue::query()->listedPage(new ItemsPerPage(fake()->numberBetween(2, 100)));

    expect($listedIssuesPage->getCollection()->modelKeys())
        ->toEqual([$newerIssue->getKey(), $olderIssue->getKey()]);
});

test('listed page has the given number of issues per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);

    $listedIssuesPage = Issue::query()->listedPage(new ItemsPerPage($itemsPerPage));

    expect($listedIssuesPage->perPage())
        ->toBe($itemsPerPage);
});

test('of issue type keeps an issue of the type', function (): void {
    $issueType = fake()->randomElement(IssueType::cases());
    $issueOfType = Issue::factory()->create(['type' => $issueType]);

    $issuesOfType = Issue::query()
        ->ofIssueType($issueType)
        ->get();

    expect($issuesOfType->sole()->is($issueOfType))
        ->toBeTrue();
});

test('of issue type leaves out an issue of another type or without one', function (?IssueType $otherIssueType): void {
    Issue::factory()->create(['type' => $otherIssueType]);

    $issuesOfType = Issue::query()
        ->ofIssueType(IssueType::BUG)
        ->get();

    expect($issuesOfType)
        ->toBeEmpty();
})->with([
    'another type' => [IssueType::FEATURE],
    'no type' => [null],
]);

test('untyped keeps an issue without a type', function (): void {
    $untypedIssue = Issue::factory()->create(['type' => null]);

    $untypedIssues = Issue::query()
        ->untyped()
        ->get();

    expect($untypedIssues->sole()->is($untypedIssue))
        ->toBeTrue();
});

test('untyped leaves out an issue with a type', function (): void {
    Issue::factory()->create(['type' => fake()->randomElement(IssueType::cases())]);

    $untypedIssues = Issue::query()
        ->untyped()
        ->get();

    expect($untypedIssues)
        ->toBeEmpty();
});
