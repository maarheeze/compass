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
