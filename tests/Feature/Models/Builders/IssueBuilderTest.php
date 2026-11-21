<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\IssueTypeOrder;
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

test('oldest opened orders the issues from the first opened', function (): void {
    $newerIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-1 year')]);
    $olderIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-2 years', '-1 year')]);

    $issuesOldestOpened = Issue::query()
        ->oldestOpened()
        ->get();

    expect($issuesOldestOpened->modelKeys())
        ->toEqual([$olderIssue->id, $newerIssue->id]);
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

test('listed is the issues opened oldest first', function (): void {
    $newerIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-1 week')]);
    $olderIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);

    $listedIssues = Issue::query()->listed(new IssueTypeOrder([]));

    expect($listedIssues->modelKeys())
        ->toEqual([$olderIssue->getKey(), $newerIssue->getKey()]);
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

test('listed is the issues of the ordered types first, in the order of their types', function (): void {
    $featureIssue = Issue::factory()->create(['type' => IssueType::FEATURE]);
    $bugIssue = Issue::factory()->create(['type' => IssueType::BUG]);

    $listedIssues = Issue::query()->listed(new IssueTypeOrder([IssueType::BUG, IssueType::FEATURE]));

    expect($listedIssues->modelKeys())
        ->toEqual([$bugIssue->getKey(), $featureIssue->getKey()]);
});

test('listed is the issues of every other type or without a type after the ordered ones', function (): void {
    $otherIssue = Issue::factory()->create([
        'type' => fake()->randomElement([IssueType::FEATURE, IssueType::TASK, null]),
        'opened_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);
    $bugIssue = Issue::factory()->create([
        'type' => IssueType::BUG,
        'opened_at' => fake()->dateTimeBetween('-1 week'),
    ]);

    $listedIssues = Issue::query()->listed(new IssueTypeOrder([IssueType::BUG]));

    expect($listedIssues->modelKeys())
        ->toEqual([$bugIssue->getKey(), $otherIssue->getKey()]);
});

test('listed is the issues of one place in the order opened oldest first', function (): void {
    $newerIssue = Issue::factory()->create([
        'type' => IssueType::BUG,
        'opened_at' => fake()->dateTimeBetween('-1 week'),
    ]);
    $olderIssue = Issue::factory()->create([
        'type' => IssueType::BUG,
        'opened_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
    ]);

    $listedIssues = Issue::query()->listed(new IssueTypeOrder([IssueType::BUG]));

    expect($listedIssues->modelKeys())
        ->toEqual([$olderIssue->getKey(), $newerIssue->getKey()]);
});

test('listed page is the issues of the ordered types first', function (): void {
    $featureIssue = Issue::factory()->create(['type' => IssueType::FEATURE]);
    $bugIssue = Issue::factory()->create(['type' => IssueType::BUG]);

    $listedIssuesPage = Issue::query()->listedPage(
        new ItemsPerPage(fake()->numberBetween(2, 100)),
        new IssueTypeOrder([IssueType::BUG, IssueType::FEATURE]),
    );

    expect($listedIssuesPage->getCollection()->modelKeys())
        ->toEqual([$bugIssue->getKey(), $featureIssue->getKey()]);
});

test('listed page is the issues opened oldest first', function (): void {
    $newerIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-1 week')]);
    $olderIssue = Issue::factory()->create(['opened_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);

    $listedIssuesPage = Issue::query()->listedPage(
        new ItemsPerPage(fake()->numberBetween(2, 100)),
        new IssueTypeOrder([]),
    );

    expect($listedIssuesPage->getCollection()->modelKeys())
        ->toEqual([$olderIssue->getKey(), $newerIssue->getKey()]);
});

test('listed page has the given number of issues per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);

    $listedIssuesPage = Issue::query()->listedPage(new ItemsPerPage($itemsPerPage), new IssueTypeOrder([]));

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
