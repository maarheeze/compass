<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\PullRequestAssigneeCollection;
use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\View\FilterOption;

test('assignee filter options count the pull requests per assignee', function (): void {
    $source = fake()->slug();
    $login = fake()->userName();
    $avatarUrl = fake()->imageUrl();
    $pullRequests = new PullRequestCollection([
        PullRequest::factory()
            ->make(['source' => $source])
            ->setRelation('assignees', new PullRequestAssigneeCollection([
                PullRequestAssignee::factory()->make(['login' => $login, 'avatar_url' => $avatarUrl]),
            ])),
        PullRequest::factory()
            ->make(['source' => $source])
            ->setRelation('assignees', new PullRequestAssigneeCollection([
                PullRequestAssignee::factory()->make(['login' => $login, 'avatar_url' => $avatarUrl]),
            ])),
    ]);

    $assigneeFilterOptions = $pullRequests->assigneeFilterOptions($this->app->make(Translator::class));

    expect($assigneeFilterOptions->all())
        ->toEqual([
            new FilterOption('Unassigned', 0, AssigneeFilter::unassignedRouteParameters(), null),
            new FilterOption($login, 2, new Assignee($source, $login)->routeParameters(), $avatarUrl),
        ]);
});

test('assigned to is the pull requests of the source assigned to the login', function (): void {
    $assignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection([PullRequestAssignee::factory()->make()]));
    $unassignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection());
    $pullRequests = new PullRequestCollection([$assignedPullRequest, $unassignedPullRequest]);

    $pullRequestsAssignedTo = $pullRequests->assignedTo(new Assignee($assignedPullRequest->source, $assignedPullRequest->assignees->sole()->login));

    expect($pullRequestsAssignedTo->sole())
        ->toBe($assignedPullRequest);
});

test('assigned to leaves out the pull requests of another source assigned to the same login', function (): void {
    $login = fake()->userName();
    $pullRequest = PullRequest::factory()
        ->make(['source' => fake()->unique()->slug()])
        ->setRelation('assignees', new PullRequestAssigneeCollection([PullRequestAssignee::factory()->make(['login' => $login])]));
    $pullRequests = new PullRequestCollection([$pullRequest]);

    $pullRequestsAssignedTo = $pullRequests->assignedTo(new Assignee(fake()->unique()->slug(), $login));

    expect($pullRequestsAssignedTo)
        ->toBeEmpty();
});

test('assignee filter options start with the pull requests without an assignee', function (): void {
    $pullRequests = new PullRequestCollection([
        PullRequest::factory()
            ->make()
            ->setRelation('assignees', new PullRequestAssigneeCollection([PullRequestAssignee::factory()->make()])),
        PullRequest::factory()
            ->make()
            ->setRelation('assignees', new PullRequestAssigneeCollection()),
    ]);

    $assigneeFilterOptions = $pullRequests->assigneeFilterOptions($this->app->make(Translator::class));

    expect($assigneeFilterOptions->first())
        ->toEqual(new FilterOption('Unassigned', 1, AssigneeFilter::unassignedRouteParameters(), null));
});

test('unassigned is the pull requests without an assignee', function (): void {
    $assignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection([PullRequestAssignee::factory()->make()]));
    $unassignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection());
    $pullRequests = new PullRequestCollection([$assignedPullRequest, $unassignedPullRequest]);

    $unassignedPullRequests = $pullRequests->unassigned();

    expect($unassignedPullRequests->sole())
        ->toBe($unassignedPullRequest);
});
