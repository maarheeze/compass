<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\Issues\CollectedIssue;

use function fake;

class CollectedIssueFactory
{
    public static function make(): CollectedIssue
    {
        return new CollectedIssue(
            fake()->unique()->sha1(),
            fake()->slug(),
            fake()->numberBetween(1, 9999),
            fake()->sentence(),
            fake()->url(),
            fake()->userName(),
            CarbonImmutable::parse(fake()->iso8601()),
            [fake()->unique()->userName()],
        );
    }
}
