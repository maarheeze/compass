<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\IssueAssignee;

test('avatar name is the login', function (): void {
    $assignee = IssueAssignee::factory()->create();

    expect($assignee->avatarName())
        ->toBe($assignee->login);
});
