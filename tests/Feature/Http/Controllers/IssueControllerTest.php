<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('index contains the open issues', function (): void {
    $issue = Issue::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_ISSUES));

    $response->assertViewHas('issues', static function (IssueCollection $viewIssues) use ($issue): bool {
        return $viewIssues->sole()->is($issue);
    });
});

test('assigned contains the issues assigned to the signed in user', function (): void {
    $user = User::factory()->create(['nickname' => fake()->unique()->userName()]);
    $issue = Issue::factory()->create();
    IssueAssignee::factory()->create([
        'issue_id' => $issue->id,
        'login' => $user->nickname,
    ]);
    IssueAssignee::factory()->create(['login' => fake()->unique()->userName()]);
    $this->actingAs($user, AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_ISSUES_ASSIGNED));

    $response->assertViewHas('issues', static function (IssueCollection $viewIssues) use ($issue): bool {
        return $viewIssues->sole()->is($issue);
    });
});

test('index contains its route name as active route name', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_ISSUES));

    $response->assertViewHas('activeRouteName', RouteName::GITHUB_ISSUES);
});

test('assigned contains its route name as active route name', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_ISSUES_ASSIGNED));

    $response->assertViewHas('activeRouteName', RouteName::GITHUB_ISSUES_ASSIGNED);
});
