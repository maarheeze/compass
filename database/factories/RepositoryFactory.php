<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Models\Repository;

use function fake;

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
            'name' => fake()->unique()->slug(),
            'default_branch' => fake()->word(),
            'collection_run_id' => CollectionRunId::generate(),
            'is_active' => fake()->boolean(),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
