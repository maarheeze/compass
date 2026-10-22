<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Alerts;
use Maarheeze\Compass\Analyzer\GitHub\IssueCounts;
use Maarheeze\Compass\Analyzer\GitHub\PullRequestCounts;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the alerts', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('alerts', static function (Alerts $alerts) use ($site): bool {
        return $alerts->sitesBehind->sole()->is($site);
    });
});

test('show contains the issue counts of the signed in user', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    IssueAssignee::factory()->create(['login' => $user->nickname]);
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('issueCounts', static function (IssueCounts $issueCounts): bool {
        return $issueCounts->assignedIssueCount === 1;
    });
});

test('show contains the pull request counts of the signed in user', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    PullRequestAssignee::factory()->create(['login' => $user->nickname]);
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('pullRequestCounts', static function (PullRequestCounts $pullRequestCounts): bool {
        return $pullRequestCounts->assignedPullRequestCount === 1;
    });
});
