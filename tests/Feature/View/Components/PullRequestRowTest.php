<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;
use Maarheeze\Compass\View\Components\PullRequestRow;

test('assignee url is the pull requests list filtered on the assignee in the source', function (): void {
    $pullRequest = PullRequest::factory()->create();
    $assignee = PullRequestAssignee::factory()->create(['pull_request_id' => $pullRequest->id]);

    $pullRequestRow = $this->app->make(PullRequestRow::class, ['pullRequest' => $pullRequest]);
    $assigneeUrl = $pullRequestRow->assigneeUrl($assignee);

    expect($assigneeUrl)
        ->toBe(route(RouteName::GITHUB_PULL_REQUESTS, [
            'source' => $pullRequest->source,
            'assignee' => $assignee->login,
        ]));
});
