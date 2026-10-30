<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\IssueCollection;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;

test('of issues keeps an assignee of one of the issues', function (): void {
    $issue = Issue::factory()->create();
    $issueAssignee = IssueAssignee::factory()->create(['issue_id' => $issue->id]);

    $issueAssigneesOfIssues = IssueAssignee::query()
        ->ofIssues(new IssueCollection([$issue]))
        ->get();

    expect($issueAssigneesOfIssues->sole()->is($issueAssignee))
        ->toBeTrue();
});

test('of issues leaves out an assignee of another issue', function (): void {
    IssueAssignee::factory()->create();

    $issueAssigneesOfIssues = IssueAssignee::query()
        ->ofIssues(new IssueCollection([Issue::factory()->create()]))
        ->get();

    expect($issueAssigneesOfIssues)
        ->toBeEmpty();
});
