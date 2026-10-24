<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\GitHub\GitHubQueryFailed;

test('context holds the error messages github answered with', function (): void {
    $errorMessages = [fake()->sentence()];

    $gitHubQueryFailed = new GitHubQueryFailed($errorMessages, null);

    expect($gitHubQueryFailed->context())
        ->toBe(['errorMessages' => $errorMessages]);
});
