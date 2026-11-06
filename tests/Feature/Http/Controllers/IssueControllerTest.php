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

test('index on an assignee contains only the issues assigned to them', function (): void {
    $issue = Issue::factory()->create();
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);
    IssueAssignee::factory()->create(['login' => fake()->unique()->userName()]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_ISSUES, [
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

test('index refuses an assignee without a source', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_ISSUES, ['assignee' => fake()->userName()]));

    $response->assertInvalid('source');
});
