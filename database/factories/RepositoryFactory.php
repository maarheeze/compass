<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

use function fake;
use function sprintf;

/**
 * @extends Factory<Repository>
 */
class RepositoryFactory extends Factory
{
    /** @var class-string<Repository> */
    protected $model = Repository::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'source' => fake()->slug(),
            'external_id' => fake()->unique()->sha1(),
            'name' => fake()->unique()->slug(),
            'full_name' => sprintf('%s/%s', fake()->slug(), fake()->unique()->slug()),
            'url' => fake()->url(),
            'default_branch' => fake()->word(),
            'last_commit_sha' => fake()->sha1(),
            'last_commit_committed_at' => fake()->dateTime(),
            'visibility' => fake()->randomElement(RepositoryVisibility::cases()),
            'composer_json_version' => fake()->optional()->sha1(),
            'composer_lock_version' => fake()->optional()->sha1(),
            'package_json_version' => fake()->optional()->sha1(),
            'dependabot_yml_version' => fake()->optional()->sha1(),
            'run_id' => Uuid::generate(),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
