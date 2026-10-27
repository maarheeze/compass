<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\PullRequestCounts;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\PullRequestCountsCard;

test('assigned url is the page of the pull requests assigned to me', function (): void {
    $pullRequestCounts = new PullRequestCounts(fake()->randomNumber(), fake()->randomNumber());

    $pullRequestCountsCard = $this->app->make(PullRequestCountsCard::class, [
        'pullRequestCounts' => $pullRequestCounts,
    ]);

    expect($pullRequestCountsCard->assignedUrl)
        ->toBe(route(RouteName::GITHUB_PULL_REQUESTS_ASSIGNED));
});

test('open url is the page of the open pull requests', function (): void {
    $pullRequestCounts = new PullRequestCounts(fake()->randomNumber(), fake()->randomNumber());

    $pullRequestCountsCard = $this->app->make(PullRequestCountsCard::class, [
        'pullRequestCounts' => $pullRequestCounts,
    ]);

    expect($pullRequestCountsCard->openUrl)
        ->toBe(route(RouteName::GITHUB_PULL_REQUESTS));
});
