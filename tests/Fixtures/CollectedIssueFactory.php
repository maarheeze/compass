<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedAssigneeCollection;
use Maarheeze\Compass\Collector\CollectedAssignee;
use Maarheeze\Compass\Collector\Issues\CollectedIssue;
use Maarheeze\Compass\Collector\Issues\IssueType;

use function fake;
use function sprintf;

class CollectedIssueFactory
{
    public static function make(): CollectedIssue
    {
        return new CollectedIssue(
            fake()->unique()->sha1(),
            sprintf('%s/%s', fake()->slug(), fake()->slug()),
            sprintf('#%d', fake()->numberBetween(1, 9999)),
            fake()->sentence(),
            fake()->randomElement(IssueType::cases()),
            fake()->url(),
            fake()->userName(),
            CarbonImmutable::parse(fake()->iso8601()),
            new CollectedAssigneeCollection([
                new CollectedAssignee(fake()->unique()->userName(), fake()->imageUrl()),
            ]),
        );
    }
}
