<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\IssueCounts;
use Maarheeze\Compass\Analyzer\GitHub\PullRequestCounts;
use Maarheeze\Compass\Collections\AlertCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the active alerts, newest first', function (): void {
    $earlierAlert = Alert::factory()
        ->active()
        ->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterAlert = Alert::factory()
        ->active()
        ->create(['created_at' => fake()->dateTimeBetween('-1 month')]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('activeAlerts', static function (AlertCollection $activeAlerts) use ($earlierAlert, $laterAlert): bool {
        return $activeAlerts->count() === 2
            && $activeAlerts->first()->id->equals($laterAlert->id)
            && $activeAlerts->last()->id->equals($earlierAlert->id);
    });
});

test('show leaves resolved alerts out of the active alerts', function (): void {
    Alert::factory()
        ->resolved()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('activeAlerts', static function (AlertCollection $activeAlerts): bool {
        return $activeAlerts->isEmpty();
    });
});

test('show contains the resolved alerts, newest first', function (): void {
    $earlierAlert = Alert::factory()
        ->resolved()
        ->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterAlert = Alert::factory()
        ->resolved()
        ->create(['created_at' => fake()->dateTimeBetween('-1 month')]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('resolvedAlerts', static function (AlertCollection $resolvedAlerts) use ($earlierAlert, $laterAlert): bool {
        return $resolvedAlerts->count() === 2
            && $resolvedAlerts->first()->id->equals($laterAlert->id)
            && $resolvedAlerts->last()->id->equals($earlierAlert->id);
    });
});

test('show leaves done alerts out of the resolved alerts', function (): void {
    Alert::factory()
        ->done()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('resolvedAlerts', static function (AlertCollection $resolvedAlerts): bool {
        return $resolvedAlerts->isEmpty();
    });
});

test('show leaves active alerts out of the resolved alerts', function (): void {
    Alert::factory()
        ->active()
        ->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('resolvedAlerts', static function (AlertCollection $resolvedAlerts): bool {
        return $resolvedAlerts->isEmpty();
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
