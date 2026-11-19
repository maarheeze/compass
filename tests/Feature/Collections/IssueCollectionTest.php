<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\IssueAssigneeCollection;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\View\FilterOption;

test('assignee filter options count the issues per assignee', function (): void {
    $source = fake()->slug();
    $login = fake()->userName();
    $avatarUrl = fake()->imageUrl();
    $issues = new IssueCollection([
        Issue::factory()
            ->make(['source' => $source])
            ->setRelation('assignees', new IssueAssigneeCollection([
                IssueAssignee::factory()->make(['login' => $login, 'avatar_url' => $avatarUrl]),
            ])),
        Issue::factory()
            ->make(['source' => $source])
            ->setRelation('assignees', new IssueAssigneeCollection([
                IssueAssignee::factory()->make(['login' => $login, 'avatar_url' => $avatarUrl]),
            ])),
    ]);

    $assigneeFilterOptions = $issues->assigneeFilterOptions($this->app->make(Translator::class));

    expect($assigneeFilterOptions->all())
        ->toEqual([
            new FilterOption('Unassigned', 0, AssigneeFilter::unassignedRouteParameters(), null),
            new FilterOption($login, 2, new Assignee($source, $login)->routeParameters(), $avatarUrl),
        ]);
});

test('assigned to is the issues of the source assigned to the login', function (): void {
    $assignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection([IssueAssignee::factory()->make()]));
    $unassignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection());
    $issues = new IssueCollection([$assignedIssue, $unassignedIssue]);

    $issuesAssignedTo = $issues->assignedTo(new Assignee($assignedIssue->source, $assignedIssue->assignees->sole()->login));

    expect($issuesAssignedTo->sole())
        ->toBe($assignedIssue);
});

test('assigned to leaves out the issues of another source assigned to the same login', function (): void {
    $login = fake()->userName();
    $issue = Issue::factory()
        ->make(['source' => fake()->unique()->slug()])
        ->setRelation('assignees', new IssueAssigneeCollection([IssueAssignee::factory()->make(['login' => $login])]));
    $issues = new IssueCollection([$issue]);

    $issuesAssignedTo = $issues->assignedTo(new Assignee(fake()->unique()->slug(), $login));

    expect($issuesAssignedTo)
        ->toBeEmpty();
});

test('assignee filter options start with the issues without an assignee', function (): void {
    $issues = new IssueCollection([
        Issue::factory()
            ->make()
            ->setRelation('assignees', new IssueAssigneeCollection([IssueAssignee::factory()->make()])),
        Issue::factory()
            ->make()
            ->setRelation('assignees', new IssueAssigneeCollection()),
    ]);

    $assigneeFilterOptions = $issues->assigneeFilterOptions($this->app->make(Translator::class));

    expect($assigneeFilterOptions->first())
        ->toEqual(new FilterOption('Unassigned', 1, AssigneeFilter::unassignedRouteParameters(), null));
});

test('unassigned is the issues without an assignee', function (): void {
    $assignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection([IssueAssignee::factory()->make()]));
    $unassignedIssue = Issue::factory()
        ->make()
        ->setRelation('assignees', new IssueAssigneeCollection());
    $issues = new IssueCollection([$assignedIssue, $unassignedIssue]);

    $unassignedIssues = $issues->unassigned();

    expect($unassignedIssues->sole())
        ->toBe($unassignedIssue);
});
