<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\Repository;

use function fake;

/**
 * @extends Factory<PullRequest>
 */
class PullRequestFactory extends Factory
{
    /** @var class-string<PullRequest> */
    protected $model = PullRequest::class;

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
            'is_draft' => fake()->boolean(),
            'opened_at' => fake()->dateTime(),
            'collection_run_id' => CollectionRunId::generate(),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
