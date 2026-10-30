<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;

test('of pull requests keeps an assignee of one of the pull requests', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $pullRequestAssignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestAssigneesOfPullRequests = PullRequestAssignee::query()
        ->ofPullRequests(new PullRequestCollection([$pullRequest]))
        ->get();

    expect($pullRequestAssigneesOfPullRequests->sole()->is($pullRequestAssignee))
        ->toBeTrue();
});

test('of pull requests leaves out an assignee of another pull request', function (): void {
    PullRequestAssignee::factory()->create();

    $pullRequestAssigneesOfPullRequests = PullRequestAssignee::query()
        ->ofPullRequests(new PullRequestCollection([PullRequest::factory()->create()]))
        ->get();

    expect($pullRequestAssigneesOfPullRequests)
        ->toBeEmpty();
});
