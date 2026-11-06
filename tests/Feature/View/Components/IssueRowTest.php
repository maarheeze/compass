<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;
use Maarheeze\Compass\View\Components\IssueRow;

test('assignee url is the issues list filtered on the assignee in the source', function (): void {
    $issue = Issue::factory()->create();
    $assignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issueRow = $this->app->make(IssueRow::class, ['issue' => $issue]);
    $assigneeUrl = $issueRow->assigneeUrl($assignee);

    expect($assigneeUrl)
        ->toBe(route(RouteName::GITHUB_ISSUES, [
            'source' => $issue->source,
            'assignee' => $assignee->login,
        ]));
});
