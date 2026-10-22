<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\PullRequestFinder;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\User;

test('find pull requests finds the open pull requests', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestFinder = $this->app->make(PullRequestFinder::class);
    $pullRequests = $pullRequestFinder->findPullRequests();

    expect($pullRequests->sole()->is($pullRequest))
        ->toBeTrue();
});

test('find pull requests assigned to finds a pull request assigned to the user', function (): void {
    $user = User::factory()->create();
    $pullRequest = PullRequest::factory()->create();
    PullRequestAssignee::factory()->create([
        'pull_request_id' => $pullRequest->id,
        'login' => $user->nickname,
    ]);

    $pullRequestFinder = $this->app->make(PullRequestFinder::class);
    $pullRequestsAssignedToUser = $pullRequestFinder->findPullRequestsAssignedTo($user);

    expect($pullRequestsAssignedToUser->sole()->is($pullRequest))
        ->toBeTrue();
});

test('find pull requests assigned to leaves out a pull request assigned to someone else', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    PullRequestAssignee::factory()->create(['login' => fake()->unique()->userName()]);

    $pullRequestFinder = $this->app->make(PullRequestFinder::class);
    $pullRequestsAssignedToUser = $pullRequestFinder->findPullRequestsAssignedTo($user);

    expect($pullRequestsAssignedToUser)
        ->toBeEmpty();
});
