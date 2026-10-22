<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\GitHub\GitHubIssue;

use function fake;

class GitHubIssueFactory
{
    public static function make(): GitHubIssue
    {
        return new GitHubIssue(
            fake()->unique()->sha1(),
            fake()->numberBetween(1, 9999),
            fake()->sentence(),
            fake()->url(),
            fake()->userName(),
            CarbonImmutable::parse(fake()->iso8601()),
            [fake()->unique()->userName()],
        );
    }
}
