<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\PullRequestAssignee;

test('avatar name is the login', function (): void {
    $assignee = PullRequestAssignee::factory()->create();

    expect($assignee->avatarName())
        ->toBe($assignee->login);
});
