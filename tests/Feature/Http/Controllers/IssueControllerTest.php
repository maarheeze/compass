<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Filter;

test('index contains the open issues', function (): void {
    $issue = Issue::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES));

    $response->assertViewHas('issues', static function (IssueCollection $viewIssues) use ($issue): bool {
        return $viewIssues->sole()->is($issue);
    });
});

test('index on an assignee contains only the issues assigned to them', function (): void {
    $issue = Issue::factory()->create();
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);
    IssueAssignee::factory()->create(['login' => fake()->unique()->userName()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, [
        'source' => $issue->source,
        'assignee' => $assignee->login,
    ]));

    $response->assertViewHas(
        'issues',
        static function (IssueCollection $viewIssues) use ($issue): bool {
            return $viewIssues->sole()->is($issue);
        },
    );
});

test('index on an assignee contains an assignees filter with the assignees of all open issues', function (): void {
    $issue = Issue::factory()->create();
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);
    $otherAssignee = IssueAssignee::factory()->create(['login' => fake()->unique()->userName()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, [
        'source' => $issue->source,
        'assignee' => $assignee->login,
    ]));

    $response->assertViewHas('assigneesFilter', static function (Filter $viewAssigneesFilter) use ($otherAssignee): bool {
        return $viewAssigneesFilter->options->contains('label', $otherAssignee->login);
    });
});

test('index on unassigned contains only the issues without an assignee', function (): void {
    $unassignedIssue = Issue::factory()->create();
    IssueAssignee::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, AssigneeFilter::unassignedRouteParameters()));

    $response->assertViewHas('issues', static function (IssueCollection $viewIssues) use ($unassignedIssue): bool {
        return $viewIssues->sole()->is($unassignedIssue);
    });
});

test('index refuses an unassigned value other than true', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, [AssigneeFilter::UNASSIGNED_PARAMETER => fake()->word()]));

    $response->assertInvalid(AssigneeFilter::UNASSIGNED_PARAMETER);
});

test('index refuses an assignee without a source', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, ['assignee' => fake()->userName()]));

    $response->assertInvalid('source');
});
