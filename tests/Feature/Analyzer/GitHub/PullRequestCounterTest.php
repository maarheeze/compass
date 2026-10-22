<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\PullRequestCounter;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\User;

test('count counts the open pull requests', function (): void {
    $openPullRequestCount = fake()->numberBetween(1, 5);
    PullRequest::factory()->count($openPullRequestCount)->create();

    $pullRequestCounter = $this->app->make(PullRequestCounter::class);
    $pullRequestCounts = $pullRequestCounter->count(User::factory()->create());

    expect($pullRequestCounts->openPullRequestCount)
        ->toBe($openPullRequestCount);
});

test('count counts the pull requests assigned to the user', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    PullRequestAssignee::factory()->create(['login' => $user->nickname]);
    PullRequestAssignee::factory()->create(['login' => fake()->unique()->userName()]);

    $pullRequestCounter = $this->app->make(PullRequestCounter::class);
    $pullRequestCounts = $pullRequestCounter->count($user);

    expect($pullRequestCounts->assignedPullRequestCount)
        ->toBe(1);
});
