<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Collections\IssueAssigneeCollection;
use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\Assignee;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\IssueTypeFilter;
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

test('issue type filter options count the issues per type in display order, then those without one', function (): void {
    $issues = new IssueCollection([
        Issue::factory()->make(['type' => IssueType::FEATURE]),
        Issue::factory()->make(['type' => IssueType::FEATURE]),
        Issue::factory()->make(['type' => null]),
    ]);

    $issueTypeFilterOptions = $issues->issueTypeFilterOptions($this->app->make(Translator::class));

    expect($issueTypeFilterOptions->all())
        ->toEqual([
            new FilterOption('Bug', 0, IssueTypeFilter::issueTypeRouteParameters(IssueType::BUG), null),
            new FilterOption('Feature', 2, IssueTypeFilter::issueTypeRouteParameters(IssueType::FEATURE), null),
            new FilterOption('Task', 0, IssueTypeFilter::issueTypeRouteParameters(IssueType::TASK), null),
            new FilterOption('No type', 1, IssueTypeFilter::untypedRouteParameters(), null),
        ]);
});

test('of issue type is the issues of that type', function (): void {
    $bugIssue = Issue::factory()->make(['type' => IssueType::BUG]);
    $issues = new IssueCollection([
        $bugIssue,
        Issue::factory()->make(['type' => fake()->randomElement([IssueType::FEATURE, IssueType::TASK, null])]),
    ]);

    $bugIssues = $issues->ofIssueType(IssueType::BUG);

    expect($bugIssues->sole())
        ->toBe($bugIssue);
});

test('untyped is the issues without a type', function (): void {
    $untypedIssue = Issue::factory()->make(['type' => null]);
    $issues = new IssueCollection([
        $untypedIssue,
        Issue::factory()->make(['type' => fake()->randomElement(IssueType::cases())]),
    ]);

    $untypedIssues = $issues->untyped();

    expect($untypedIssues->sole())
        ->toBe($untypedIssue);
});
