<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Drivers\GitHub\GitHubAnsweredWithError;

test('github answered with error holds the error message github answered with', function (): void {
    $errorMessage = fake()->sentence();

    $gitHubAnsweredWithError = new GitHubAnsweredWithError($errorMessage, null);

    expect($gitHubAnsweredWithError->getMessage())
        ->toBe($errorMessage);
});
