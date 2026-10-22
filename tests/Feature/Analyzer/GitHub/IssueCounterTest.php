<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\IssueCounter;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\User;

test('count counts the open issues', function (): void {
    $openIssueCount = fake()->numberBetween(1, 5);
    Issue::factory()->count($openIssueCount)->create();

    $issueCounter = $this->app->make(IssueCounter::class);
    $issueCounts = $issueCounter->count(User::factory()->create());

    expect($issueCounts->openIssueCount)
        ->toBe($openIssueCount);
});

test('count counts the issues assigned to the user', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    IssueAssignee::factory()->create(['login' => $user->nickname]);
    IssueAssignee::factory()->create(['login' => fake()->unique()->userName()]);

    $issueCounter = $this->app->make(IssueCounter::class);
    $issueCounts = $issueCounter->count($user);

    expect($issueCounts->assignedIssueCount)
        ->toBe(1);
});
