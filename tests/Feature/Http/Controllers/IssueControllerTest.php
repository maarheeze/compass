<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\IssueTypeFilter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Filter;
use Maarheeze\Compass\View\FilterOption;

test('index contains the open issues', function (): void {
    $issue = Issue::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES));

    $response->assertViewHas('issues', static function (LengthAwarePaginator $viewIssues) use ($issue): bool {
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
        static function (LengthAwarePaginator $viewIssues) use ($issue): bool {
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

    $response->assertViewHas('issues', static function (LengthAwarePaginator $viewIssues) use ($unassignedIssue): bool {
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

test('index orders the issues by the configured issue type order', function (): void {
    $featureIssue = Issue::factory()->create(['type' => IssueType::FEATURE]);
    $bugIssue = Issue::factory()->create(['type' => IssueType::BUG]);
    $this->setConfiguration('compass.issue_type_order', [IssueType::BUG, IssueType::FEATURE]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES));

    $response->assertViewHas(
        'issues',
        static function (LengthAwarePaginator $viewIssues) use ($bugIssue, $featureIssue): bool {
            return $viewIssues->getCollection()
                ->first()
                ->is($bugIssue)
                && $viewIssues->getCollection()
                ->last()
                ->is($featureIssue);
        },
    );
});

test('index on an issue type contains only the issues of that type', function (): void {
    $bugIssue = Issue::factory()->create(['type' => IssueType::BUG]);
    Issue::factory()->create(['type' => fake()->randomElement([IssueType::FEATURE, IssueType::TASK, null])]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, IssueTypeFilter::issueTypeRouteParameters(IssueType::BUG)));

    $response->assertViewHas('issues', static function (LengthAwarePaginator $viewIssues) use ($bugIssue): bool {
        return $viewIssues->sole()->is($bugIssue);
    });
});

test('index on untyped contains only the issues without a type', function (): void {
    $untypedIssue = Issue::factory()->create(['type' => null]);
    Issue::factory()->create(['type' => fake()->randomElement(IssueType::cases())]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, IssueTypeFilter::untypedRouteParameters()));

    $response->assertViewHas('issues', static function (LengthAwarePaginator $viewIssues) use ($untypedIssue): bool {
        return $viewIssues->sole()->is($untypedIssue);
    });
});

test('index on an issue type and an assignee contains only the issues of both', function (): void {
    $issue = Issue::factory()->create(['type' => IssueType::BUG]);
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);
    Issue::factory()->create(['type' => IssueType::BUG]);
    $otherTypeIssue = Issue::factory()->create([
        'source' => $issue->source,
        'type' => IssueType::FEATURE,
    ]);
    IssueAssignee::factory()->create([
        'issue_id' => $otherTypeIssue->id,
        'login' => $assignee->login,
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, [
        'source' => $issue->source,
        'assignee' => $assignee->login,
        ...IssueTypeFilter::issueTypeRouteParameters(IssueType::BUG),
    ]));

    $response->assertViewHas('issues', static function (LengthAwarePaginator $viewIssues) use ($issue): bool {
        return $viewIssues->sole()->is($issue);
    });
});

test('index on an issue type contains an issue types filter counting all open issues', function (): void {
    Issue::factory()->create(['type' => IssueType::FEATURE]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, IssueTypeFilter::issueTypeRouteParameters(IssueType::BUG)));

    $response->assertViewHas('issueTypesFilter', static function (Filter $viewIssueTypesFilter): bool {
        $featureQueryParameters = IssueTypeFilter::issueTypeRouteParameters(IssueType::FEATURE);
        $featureOption = $viewIssueTypesFilter->options->firstOrFail(
            static function (FilterOption $filterOption) use ($featureQueryParameters): bool {
                return $filterOption->queryParameters === $featureQueryParameters;
            },
        );

        return $featureOption->count === 1;
    });
});

test('index refuses an unknown issue type', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, [IssueTypeFilter::TYPE_PARAMETER => fake()->word()]));

    $response->assertInvalid(IssueTypeFilter::TYPE_PARAMETER);
});

test('index refuses an untyped value other than true', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, [IssueTypeFilter::UNTYPED_PARAMETER => fake()->word()]));

    $response->assertInvalid(IssueTypeFilter::UNTYPED_PARAMETER);
});

test('index shows the configured number of issues per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);
    $this->setConfiguration('compass.items_per_page', $itemsPerPage);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES));

    $response->assertViewHas('issues', static function (LengthAwarePaginator $viewIssues) use ($itemsPerPage): bool {
        return $viewIssues->perPage() === $itemsPerPage;
    });
});

test('index on an assignee keeps the assignee on the other pages', function (): void {
    $issue = Issue::factory()->create();
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::ISSUES, [
        'source' => $issue->source,
        'assignee' => $assignee->login,
    ]));

    $response->assertViewHas('issues', static function (LengthAwarePaginator $viewIssues) use ($issue, $assignee): bool {
        return $viewIssues->url(2) === route(RouteName::ISSUES, [
            'source' => $issue->source,
            'assignee' => $assignee->login,
            'page' => 2,
        ]);
    });
});
