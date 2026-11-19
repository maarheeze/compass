<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\FilterOptionCollection;
use Maarheeze\Compass\Collections\IssueAssigneeCollection;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Collections\PullRequestAssigneeCollection;
use Maarheeze\Compass\Collections\PullRequestCollection;
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
    $issues = new IssueCollection([
        Issue::factory()->make(),
        Issue::factory()->make(),
    ]);
    $assigneeFilter = AssigneeFilter::everyone();

    $filteredIssues = $assigneeFilter->issues($issues);

    expect($filteredIssues->all())
        ->toBe($issues->all());
});

test('issues on an assignee are the issues assigned to them', function (): void {
    $assignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection([IssueAssignee::factory()->make()]));
    $unassignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection());
    $assigneeFilter = AssigneeFilter::onAssignee(new Assignee($assignedIssue->source, $assignedIssue->assignees->sole()->login));

    $filteredIssues = $assigneeFilter->issues(new IssueCollection([$assignedIssue, $unassignedIssue]));

    expect($filteredIssues->sole())
        ->toBe($assignedIssue);
});

test('pull requests for everyone are all the pull requests', function (): void {
    $pullRequests = new PullRequestCollection([
        PullRequest::factory()->make(),
        PullRequest::factory()->make(),
    ]);
    $assigneeFilter = AssigneeFilter::everyone();

    $filteredPullRequests = $assigneeFilter->pullRequests($pullRequests);

    expect($filteredPullRequests->all())
        ->toBe($pullRequests->all());
});

test('pull requests on an assignee are the pull requests assigned to them', function (): void {
    $assignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection([PullRequestAssignee::factory()->make()]));
    $unassignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection());
    $assigneeFilter = AssigneeFilter::onAssignee(new Assignee($assignedPullRequest->source, $assignedPullRequest->assignees->sole()->login));

    $filteredPullRequests = $assigneeFilter->pullRequests(new PullRequestCollection([$assignedPullRequest, $unassignedPullRequest]));

    expect($filteredPullRequests->sole())
        ->toBe($assignedPullRequest);
});

test('issues unassigned are the issues without an assignee', function (): void {
    $assignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection([IssueAssignee::factory()->make()]));
    $unassignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection());
    $assigneeFilter = AssigneeFilter::unassigned();

    $filteredIssues = $assigneeFilter->issues(new IssueCollection([$assignedIssue, $unassignedIssue]));

    expect($filteredIssues->sole())
        ->toBe($unassignedIssue);
});

test('pull requests unassigned are the pull requests without an assignee', function (): void {
    $assignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection([PullRequestAssignee::factory()->make()]));
    $unassignedPullRequest = PullRequest::factory()
        ->make()
        ->setRelation('assignees', new PullRequestAssigneeCollection());
    $assigneeFilter = AssigneeFilter::unassigned();

    $filteredPullRequests = $assigneeFilter->pullRequests(new PullRequestCollection([$assignedPullRequest, $unassignedPullRequest]));

    expect($filteredPullRequests->sole())
        ->toBe($unassignedPullRequest);
});

test('unassigned route parameters filter on unassigned', function (): void {
    $unassignedRouteParameters = AssigneeFilter::unassignedRouteParameters();

    expect($unassignedRouteParameters)
        ->toBe([AssigneeFilter::UNASSIGNED_PARAMETER => AssigneeFilter::UNASSIGNED_VALUE]);
});
