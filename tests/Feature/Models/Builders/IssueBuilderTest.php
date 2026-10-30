<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Uuid\Uuid;

test('assigned to keeps a issue assigned to the user', function (): void {
    $user = User::factory()->create();
    $issue = Issue::factory()->create();
    IssueAssignee::factory()->create([
        'issue_id' => $issue->id,
        'login' => $user->nickname,
    ]);

    $issuesAssignedToUser = Issue::query()
        ->assignedTo($user)
        ->get();

    expect($issuesAssignedToUser->sole()->is($issue))
        ->toBeTrue();
});

test('assigned to leaves out a issue assigned to someone else', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    IssueAssignee::factory()->create(['login' => fake()->unique()->userName()]);

    $issuesAssignedToUser = Issue::query()
        ->assignedTo($user)
        ->get();

    expect($issuesAssignedToUser)
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
