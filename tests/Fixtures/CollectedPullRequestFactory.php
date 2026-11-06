<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\PullRequests\CollectedPullRequest;

use function fake;
use function sprintf;

class CollectedPullRequestFactory
{
    public static function make(): CollectedPullRequest
    {
        return new CollectedPullRequest(
            fake()->unique()->sha1(),
            sprintf('%s/%s', fake()->slug(), fake()->slug()),
            fake()->numberBetween(1, 9999),
            fake()->sentence(),
            fake()->url(),
            fake()->userName(),
            fake()->boolean(),
            CarbonImmutable::parse(fake()->iso8601()),
            [fake()->unique()->userName()],
        );
    }
}
