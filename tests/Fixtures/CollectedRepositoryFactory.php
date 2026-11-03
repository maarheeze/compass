<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\Repositories\CollectedRepository;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;

use function fake;

class CollectedRepositoryFactory
{
    public static function make(): CollectedRepository
    {
        return self::withVisibility(fake()->randomElement(RepositoryVisibility::cases()));
    }

    public static function withVisibility(RepositoryVisibility $visibility): CollectedRepository
    {
        return new CollectedRepository(
            fake()->sha1(),
            fake()->slug(),
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
