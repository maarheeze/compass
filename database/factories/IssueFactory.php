<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Uuid\Uuid;

use function fake;

/**
 * @extends Factory<Issue>
 */
class IssueFactory extends Factory
{
    /** @var class-string<Issue> */
    protected $model = Issue::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repository_id' => Repository::factory(),
            'github_id' => fake()->unique()->sha1(),
            'number' => fake()->numberBetween(1, 9999),
            'title' => fake()->sentence(),
            'url' => fake()->url(),
            'author_login' => fake()->optional()->userName(),
            'opened_at' => fake()->dateTime(),
            'run_id' => Uuid::generate(),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
