<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Tests\Fixtures;

use Maarheeze\Compass\Collector\GitHub\GitHubRepository;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Models\Repository;

use function fake;

class GitHubRepositoryFactory
{
    public static function forRepository(Repository $repository): GitHubRepository
    {
        return new GitHubRepository(
            $repository->github_id,
            $repository->name,
            $repository->url,
            fake()->unique()->word(),
            fake()->sha1(),
            fake()->randomElement(RepositoryVisibility::cases()),
            $repository->composer_json_oid,
            $repository->composer_lock_oid,
        );
    }

    public static function make(): GitHubRepository
    {
        return new GitHubRepository(
            fake()->sha1(),
            fake()->slug(),
            fake()->url(),
            fake()->word(),
            fake()->sha1(),
            fake()->randomElement(RepositoryVisibility::cases()),
            fake()->sha1(),
            fake()->sha1(),
        );
    }
}
