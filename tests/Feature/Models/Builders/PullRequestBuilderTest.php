<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Uuid\Uuid;

test('assigned to keeps a pull request assigned to the user', function (): void {
    $user = User::factory()->create();
    $pullRequest = PullRequest::factory()->create();
    PullRequestAssignee::factory()->create([
        'pull_request_id' => $pullRequest->id,
        'login' => $user->nickname,
    ]);

    $pullRequestsAssignedToUser = PullRequest::query()
        ->assignedTo($user)
        ->get();

    expect($pullRequestsAssignedToUser->sole()->is($pullRequest))
        ->toBeTrue();
});

test('assigned to leaves out a pull request assigned to someone else', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    PullRequestAssignee::factory()->create(['login' => fake()->unique()->userName()]);

    $pullRequestsAssignedToUser = PullRequest::query()
        ->assignedTo($user)
        ->get();

    expect($pullRequestsAssignedToUser)
        ->toBeEmpty();
});

test('not returned by run keeps a pullRequest returned by another run', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestsNotReturnedByRun = PullRequest::query()
        ->notReturnedByRun(Uuid::generate())
        ->get();

    expect($pullRequestsNotReturnedByRun->sole()->is($pullRequest))
        ->toBeTrue();
});

test('not returned by run leaves out a pullRequest returned by the run', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestsNotReturnedByRun = PullRequest::query()
        ->notReturnedByRun($pullRequest->run_id)
        ->get();

    expect($pullRequestsNotReturnedByRun)
        ->toBeEmpty();
});

test('of git hub ids keeps a pullRequest with one of the ids', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestsOfGitHubIds = PullRequest::query()
        ->ofGitHubIds(new Collection([$pullRequest->github_id]))
        ->get();

    expect($pullRequestsOfGitHubIds->sole()->is($pullRequest))
        ->toBeTrue();
});

test('of git hub ids leaves out a pullRequest with another id', function (): void {
    PullRequest::factory()->create();

    $pullRequestsOfGitHubIds = PullRequest::query()
        ->ofGitHubIds(new Collection([fake()->unique()->sha1()]))
        ->get();

    expect($pullRequestsOfGitHubIds)
        ->toBeEmpty();
});

test('latest opened orders the pull requests from the most recently opened', function (): void {
    $olderPullRequest = PullRequest::factory()->create(['opened_at' => fake()->dateTimeBetween('-2 years', '-1 year')]);
    $newerPullRequest = PullRequest::factory()->create(['opened_at' => fake()->dateTimeBetween('-1 year')]);

    $pullRequestsLatestOpened = PullRequest::query()
        ->latestOpened()
        ->get();

    expect($pullRequestsLatestOpened->modelKeys())
        ->toEqual([$newerPullRequest->id, $olderPullRequest->id]);
});
