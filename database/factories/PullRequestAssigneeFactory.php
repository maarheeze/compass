<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\PullRequest;
use Maarheeze\Compass\Models\PullRequestAssignee;

use function fake;

/**
 * @extends Factory<PullRequestAssignee>
 */
class PullRequestAssigneeFactory extends Factory
{
    /** @var class-string<PullRequestAssignee> */
    protected $model = PullRequestAssignee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'pull_request_id' => PullRequest::factory(),
            'login' => fake()->userName(),
        ];
    }
}
