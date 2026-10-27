<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\GitHub\IssueCounts;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\View\Components\IssueCountsCard;

test('assigned url is the page of the issues assigned to me', function (): void {
    $issueCounts = new IssueCounts(fake()->randomNumber(), fake()->randomNumber());

    $issueCountsCard = $this->app->make(IssueCountsCard::class, ['issueCounts' => $issueCounts]);

    expect($issueCountsCard->assignedUrl)
        ->toBe(route(RouteName::GITHUB_ISSUES_ASSIGNED));
});

test('open url is the page of the open issues', function (): void {
    $issueCounts = new IssueCounts(fake()->randomNumber(), fake()->randomNumber());

    $issueCountsCard = $this->app->make(IssueCountsCard::class, ['issueCounts' => $issueCounts]);

    expect($issueCountsCard->openUrl)
        ->toBe(route(RouteName::GITHUB_ISSUES));
});
