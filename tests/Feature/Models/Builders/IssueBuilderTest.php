<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Uuid\Uuid;

test('assigned to keeps a issue of the source assigned to the login', function (): void {
    $issue = Issue::factory()->create();
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issuesAssignedToLogin = Issue::query()
        ->assignedTo($issue->source, $assignee->login)
        ->get();

    expect($issuesAssignedToLogin->sole()->is($issue))
        ->toBeTrue();
});

test('assigned to leaves out a issue assigned to someone else', function (): void {
    $issue = Issue::factory()->create();
    IssueAssignee::factory()->create([
        'issue_id' => $issue->id,
        'login' => fake()->unique()->userName(),
    ]);

    $issuesAssignedToLogin = Issue::query()
        ->assignedTo($issue->source, fake()->unique()->userName())
        ->get();

    expect($issuesAssignedToLogin)
        ->toBeEmpty();
});

test('assigned to leaves out a issue of another source', function (): void {
    $issue = Issue::factory()->create(['source' => fake()->unique()->slug()]);
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issuesAssignedToLogin = Issue::query()
        ->assignedTo(fake()->unique()->slug(), $assignee->login)
        ->get();

    expect($issuesAssignedToLogin)
        ->toBeEmpty();
});

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
