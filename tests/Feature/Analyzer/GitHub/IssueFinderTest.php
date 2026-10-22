<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\IssueFinder;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\User;

test('find issues finds the open issues', function (): void {
    $issue = Issue::factory()->create();

    $issueFinder = $this->app->make(IssueFinder::class);
    $issues = $issueFinder->findIssues();

    expect($issues->sole()->is($issue))
        ->toBeTrue();
});

test('find issues assigned to finds an issue assigned to the user', function (): void {
    $user = User::factory()->create();
    $issue = Issue::factory()->create();
    IssueAssignee::factory()->create([
        'issue_id' => $issue->id,
        'login' => $user->nickname,
    ]);

    $issueFinder = $this->app->make(IssueFinder::class);
    $issuesAssignedToUser = $issueFinder->findIssuesAssignedTo($user);

    expect($issuesAssignedToUser->sole()->is($issue))
        ->toBeTrue();
});

test('find issues assigned to leaves out an issue assigned to someone else', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    IssueAssignee::factory()->create(['login' => fake()->unique()->userName()]);

    $issueFinder = $this->app->make(IssueFinder::class);
    $issuesAssignedToUser = $issueFinder->findIssuesAssignedTo($user);

    expect($issuesAssignedToUser)
        ->toBeEmpty();
});
