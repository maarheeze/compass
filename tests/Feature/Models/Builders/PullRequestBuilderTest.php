<?php

declare(strict_types=1);

use Illuminate\Support\Collection;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Uuid\Uuid;

test('assigned to keeps a pull request of the source assigned to the login', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestsAssignedToLogin = PullRequest::query()
        ->assignedTo($pullRequest->source, $assignee->login)
        ->get();

    expect($pullRequestsAssignedToLogin->sole()->is($pullRequest))
        ->toBeTrue();
});

test('assigned to leaves out a pull request assigned to someone else', function (): void {
    $pullRequest = PullRequest::factory()->create();
    PullRequestAssignee::factory()->create([
        'pull_request_id' => $pullRequest->id,
        'login' => fake()->unique()->userName(),
    ]);

    $pullRequestsAssignedToLogin = PullRequest::query()
        ->assignedTo($pullRequest->source, fake()->unique()->userName())
        ->get();

    expect($pullRequestsAssignedToLogin)
        ->toBeEmpty();
});

test('assigned to leaves out a pull request of another source', function (): void {
    $pullRequest = PullRequest::factory()->create(['source' => fake()->unique()->slug()]);
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestsAssignedToLogin = PullRequest::query()
        ->assignedTo(fake()->unique()->slug(), $assignee->login)
        ->get();

    expect($pullRequestsAssignedToLogin)
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

test('of external ids keeps a pullRequest with one of the ids', function (): void {
    $pullRequest = PullRequest::factory()->create();

    $pullRequestsOfExternalIds = PullRequest::query()
        ->ofExternalIds(new Collection([$pullRequest->external_id]))
        ->get();

    expect($pullRequestsOfExternalIds->sole()->is($pullRequest))
        ->toBeTrue();
});

test('of external ids leaves out a pullRequest with another id', function (): void {
    PullRequest::factory()->create();

    $pullRequestsOfExternalIds = PullRequest::query()
        ->ofExternalIds(new Collection([fake()->unique()->sha1()]))
        ->get();

    expect($pullRequestsOfExternalIds)
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

test('of source keeps a pull request of the source', function (): void {
    $pullRequestOfSource = PullRequest::factory()->create();

    $pullRequestsOfSource = PullRequest::query()
        ->ofSource($pullRequestOfSource->source)
        ->get();

    expect($pullRequestsOfSource->sole()->is($pullRequestOfSource))
        ->toBeTrue();
});

test('of source leaves out a pull request of another source', function (): void {
    PullRequest::factory()->create(['source' => fake()->unique()->slug()]);

    $pullRequestsOfSource = PullRequest::query()
        ->ofSource(fake()->unique()->slug())
        ->get();

    expect($pullRequestsOfSource)
        ->toBeEmpty();
});
