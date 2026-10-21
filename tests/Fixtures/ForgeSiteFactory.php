<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\Forge\DeploymentStatus;
use Maarheeze\Compass\Collector\Forge\ForgeDeployment;
use Maarheeze\Compass\Collector\Forge\ForgeSite;
use Maarheeze\Compass\Collector\Forge\SiteStatus;
use Maarheeze\Compass\Models\Site;

use function fake;

class ForgeSiteFactory
{
    public static function forSite(Site $site): ForgeSite
    {
        return new ForgeSite(
            $site->forge_id,
            $site->name,
            fake()->url(),
            fake()->randomElement(SiteStatus::cases()),
            fake()->numerify('php8#'),
            fake()->url(),
            fake()->word(),
            fake()->slug(),
            fake()->slug(),
            fake()->ipv4(),
            null,
        );
    }

    public static function make(): ForgeSite
    {
        return new ForgeSite(
            fake()->unique()->numerify('#######'),
            fake()->domainName(),
            fake()->url(),
            fake()->randomElement(SiteStatus::cases()),
            fake()->numerify('php8#'),
            fake()->url(),
            fake()->word(),
            fake()->slug(),
            fake()->slug(),
            fake()->ipv4(),
            new ForgeDeployment(
                fake()->randomElement(DeploymentStatus::cases()),
                fake()->sha1(),
                fake()->sentence(),
                CarbonImmutable::parse(fake()->iso8601()),
                CarbonImmutable::parse(fake()->iso8601()),
            ),
        );
    }

    public static function makeNeverDeployed(): ForgeSite
    {
        return new ForgeSite(
            fake()->unique()->numerify('#######'),
            fake()->domainName(),
            fake()->url(),
            fake()->randomElement(SiteStatus::cases()),
            fake()->numerify('php8#'),
            fake()->url(),
            fake()->word(),
            fake()->slug(),
            fake()->slug(),
            fake()->ipv4(),
            null,
        );
    }
}
