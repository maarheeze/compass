<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\Project;

use function fake;

/**
 * @extends Factory<Project>
 */
class ProjectFactory extends Factory
{
    /** @var class-string<Project> */
    protected $model = Project::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repository' => fake()->unique()->slug(),
            'default_branch' => fake()->word(),
            'commit_sha' => fake()->sha1(),
            'committed_at' => fake()->dateTime(),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
