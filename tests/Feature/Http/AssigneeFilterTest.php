<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;

test('assignees filter is filtered on the source and the assignee or on unassigned', function (): void {
    $assigneesFilter = AssigneeFilter::assigneesFilter(new FilterOptionCollection(), $this->app->make(Translator::class));

    expect($assigneesFilter->parameterNames)
        ->toBe([AssigneeFilter::SOURCE_PARAMETER, AssigneeFilter::ASSIGNEE_PARAMETER, AssigneeFilter::UNASSIGNED_PARAMETER]);
});

test('assignees filter holds the assignee filter options', function (): void {
    $assigneeFilterOptions = new FilterOptionCollection();

    $assigneesFilter = AssigneeFilter::assigneesFilter($assigneeFilterOptions, $this->app->make(Translator::class));

    expect($assigneesFilter->options)
        ->toBe($assigneeFilterOptions);
});

test('issues for everyone are all the issues', function (): void {
    $issues = Issue::factory()
        ->count(2)
        ->create();
    $assigneeFilter = AssigneeFilter::everyone();

    $filteredIssues = $assigneeFilter->issues(Issue::query())->get();

    expect($filteredIssues->modelKeys())
        ->toEqualCanonicalizing($issues->modelKeys());
});

test('issues on an assignee are the issues assigned to them', function (): void {
    $assignedIssue = Issue::factory()->create();
    $issueAssignee = IssueAssignee::factory()->create(['issue_id' => $assignedIssue->id]);
    Issue::factory()->create();
    $assigneeFilter = AssigneeFilter::onAssignee(new Assignee($assignedIssue->source, $issueAssignee->login));

    $filteredIssues = $assigneeFilter->issues(Issue::query())->get();

    expect($filteredIssues->sole()->is($assignedIssue))
        ->toBeTrue();
});

test('pull requests for everyone are all the pull requests', function (): void {
    $pullRequests = PullRequest::factory()
        ->count(2)
        ->create();
    $assigneeFilter = AssigneeFilter::everyone();

    $filteredPullRequests = $assigneeFilter->pullRequests(PullRequest::query())->get();

    expect($filteredPullRequests->modelKeys())
        ->toEqualCanonicalizing($pullRequests->modelKeys());
});

test('pull requests on an assignee are the pull requests assigned to them', function (): void {
    $assignedPullRequest = PullRequest::factory()->create();
    $pullRequestAssignee = PullRequestAssignee::factory()->create(['pull_request_id' => $assignedPullRequest->id]);
    PullRequest::factory()->create();
    $assigneeFilter = AssigneeFilter::onAssignee(new Assignee($assignedPullRequest->source, $pullRequestAssignee->login));

    $filteredPullRequests = $assigneeFilter->pullRequests(PullRequest::query())->get();

    expect($filteredPullRequests->sole()->is($assignedPullRequest))
        ->toBeTrue();
});

test('issues unassigned are the issues without an assignee', function (): void {
    IssueAssignee::factory()->create();
    $unassignedIssue = Issue::factory()->create();
    $assigneeFilter = AssigneeFilter::unassigned();

    $filteredIssues = $assigneeFilter->issues(Issue::query())->get();

    expect($filteredIssues->sole()->is($unassignedIssue))
        ->toBeTrue();
});

test('pull requests unassigned are the pull requests without an assignee', function (): void {
    PullRequestAssignee::factory()->create();
    $unassignedPullRequest = PullRequest::factory()->create();
    $assigneeFilter = AssigneeFilter::unassigned();

    $filteredPullRequests = $assigneeFilter->pullRequests(PullRequest::query())->get();

    expect($filteredPullRequests->sole()->is($unassignedPullRequest))
        ->toBeTrue();
});

test('unassigned route parameters filter on unassigned', function (): void {
    $unassignedRouteParameters = AssigneeFilter::unassignedRouteParameters();

    expect($unassignedRouteParameters)
        ->toBe([AssigneeFilter::UNASSIGNED_PARAMETER => AssigneeFilter::UNASSIGNED_VALUE]);
});
