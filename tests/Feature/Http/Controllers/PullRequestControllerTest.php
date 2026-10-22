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

    $response = $this->get(route(RouteName::GITHUB_PULL_REQUESTS));

    $response->assertViewHas(
        'pullRequests',
        static function (PullRequestCollection $viewPullRequests) use ($pullRequest): bool {
            return $viewPullRequests->sole()->is($pullRequest);
        },
    );
});

test('assigned contains the pull requests assigned to the signed in user', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    $pullRequest = PullRequest::factory()->create();
    PullRequestAssignee::factory()->create([
        'pull_request_id' => $pullRequest->id,
        'login' => $user->nickname,
    ]);
    PullRequestAssignee::factory()->create(['login' => fake()->unique()->userName()]);
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED));

    $response->assertViewHas(
        'pullRequests',
        static function (PullRequestCollection $viewPullRequests) use ($pullRequest): bool {
            return $viewPullRequests->sole()->is($pullRequest);
        },
    );
});

test('index contains its route name as active route name', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_PULL_REQUESTS));

    $response->assertViewHas('activeRouteName', RouteName::GITHUB_PULL_REQUESTS);
});

test('assigned contains its route name as active route name', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED));

    $response->assertViewHas('activeRouteName', RouteName::GITHUB_PULL_REQUESTS_ASSIGNED);
});
