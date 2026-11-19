<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Http\AssigneeFilter;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Filter;

test('index contains the open pull requests', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PULL_REQUESTS));

    $response->assertViewHas(
        'pullRequests',
        static function (PullRequestCollection $viewPullRequests) use ($pullRequest): bool {
            return $viewPullRequests->sole()->is($pullRequest);
        },
    );
});

test('index on an assignee contains only the pull requests assigned to them', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);
    PullRequestAssignee::factory()->create(['login' => fake()->unique()->userName()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PULL_REQUESTS, [
        'source' => $pullRequest->source,
        'assignee' => $assignee->login,
    ]));

    $response->assertViewHas(
        'pullRequests',
        static function (PullRequestCollection $viewPullRequests) use ($pullRequest): bool {
            return $viewPullRequests->sole()->is($pullRequest);
        },
    );
});

test('index on an assignee contains an assignees filter with the assignees of all open pull requests', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);
    $otherAssignee = PullRequestAssignee::factory()->create(['login' => fake()->unique()->userName()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PULL_REQUESTS, [
        'source' => $pullRequest->source,
        'assignee' => $assignee->login,
    ]));

    $response->assertViewHas('assigneesFilter', static function (Filter $viewAssigneesFilter) use ($otherAssignee): bool {
        return $viewAssigneesFilter->options->contains('label', $otherAssignee->login);
    });
});

test('index on unassigned contains only the pull requests without an assignee', function (): void {
    $unassignedPullRequest = PullRequest::factory()->create();
    PullRequestAssignee::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PULL_REQUESTS, AssigneeFilter::unassignedRouteParameters()));

    $response->assertViewHas('pullRequests', static function (PullRequestCollection $viewPullRequests) use ($unassignedPullRequest): bool {
        return $viewPullRequests->sole()->is($unassignedPullRequest);
    });
});

test('index refuses an unassigned value other than true', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PULL_REQUESTS, [AssigneeFilter::UNASSIGNED_PARAMETER => fake()->word()]));

    $response->assertInvalid(AssigneeFilter::UNASSIGNED_PARAMETER);
});

test('index refuses an assignee without a source', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PULL_REQUESTS, ['assignee' => fake()->userName()]));

    $response->assertInvalid('source');
});
