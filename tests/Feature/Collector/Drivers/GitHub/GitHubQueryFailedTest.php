<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubQueryFailed;

test('github query failed holds the exception behind it', function (): void {
    $exception = new RuntimeException(fake()->sentence());

    $gitHubQueryFailed = new GitHubQueryFailed($exception);

    expect($gitHubQueryFailed->getPrevious())
        ->toBe($exception);
});
