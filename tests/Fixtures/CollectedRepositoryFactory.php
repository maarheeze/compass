<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\Repositories\CollectedRepository;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Models\Repository;

use function fake;
use function sprintf;

class CollectedRepositoryFactory
{
    public static function forRepository(Repository $repository): CollectedRepository
    {
        return new CollectedRepository(
            $repository->external_id,
            $repository->name,
            $repository->full_name,
            $repository->url,
            fake()->unique()->word(),
            fake()->sha1(),
            CarbonImmutable::parse(fake()->iso8601()),
            fake()->randomElement(RepositoryVisibility::cases()),
            $repository->composer_json_version,
            $repository->composer_lock_version,
            $repository->package_json_version,
            $repository->dependabot_yml_version,
        );
    }

    public static function make(): CollectedRepository
    {
        return self::withVisibility(fake()->randomElement(RepositoryVisibility::cases()));
    }

    public static function withVisibility(RepositoryVisibility $visibility): CollectedRepository
    {
        return new CollectedRepository(
            fake()->sha1(),
            fake()->slug(),
            sprintf('%s/%s', fake()->slug(), fake()->slug()),
            fake()->url(),
            fake()->word(),
            fake()->sha1(),
            CarbonImmutable::parse(fake()->iso8601()),
            $visibility,
            fake()->sha1(),
            fake()->sha1(),
            fake()->sha1(),
            fake()->sha1(),
        );
    }
}
