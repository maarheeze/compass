<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\Sites\CollectedSite;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\Models\Site;

use function fake;

class CollectedSiteFactory
{
    public static function forSite(Site $site): CollectedSite
    {
        return new CollectedSite(
            $site->external_id,
            $site->name,
            fake()->url(),
            fake()->numerify('php8#'),
            fake()->url(),
            fake()->word(),
            fake()->optional()->boolean(),
            fake()->slug(),
            fake()->ipv4(),
            null,
            null,
            null,
            null,
        );
    }

    public static function make(): CollectedSite
    {
        return self::withDeploymentStatus(fake()->randomElement(DeploymentStatus::cases()));
    }

    public static function neverDeployed(): CollectedSite
    {
        return new CollectedSite(
            fake()->unique()->numerify('#######'),
            fake()->domainName(),
            fake()->url(),
            fake()->numerify('php8#'),
            fake()->url(),
            fake()->word(),
            fake()->optional()->boolean(),
            fake()->slug(),
            fake()->ipv4(),
            null,
            null,
            null,
            null,
        );
    }

    public static function withDeploymentStatus(DeploymentStatus $deploymentStatus): CollectedSite
    {
        return new CollectedSite(
            fake()->unique()->numerify('#######'),
            fake()->domainName(),
            fake()->url(),
            fake()->numerify('php8#'),
            fake()->url(),
            fake()->word(),
            fake()->optional()->boolean(),
            fake()->slug(),
            fake()->ipv4(),
            $deploymentStatus,
            fake()->sha1(),
            CarbonImmutable::parse(fake()->iso8601()),
            CarbonImmutable::parse(fake()->iso8601()),
        );
    }
}
