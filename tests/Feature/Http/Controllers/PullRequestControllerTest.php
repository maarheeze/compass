<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\PullRequestCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

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

test('index refuses an assignee without a source', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PULL_REQUESTS, ['assignee' => fake()->userName()]));

    $response->assertInvalid('source');
});
