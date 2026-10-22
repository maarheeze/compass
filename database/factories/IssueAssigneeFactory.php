<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\Issue;
use Maarheeze\Compass\Models\IssueAssignee;

use function fake;

/**
 * @extends Factory<IssueAssignee>
 */
class IssueAssigneeFactory extends Factory
{
    /** @var class-string<IssueAssignee> */
    protected $model = IssueAssignee::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'issue_id' => Issue::factory(),
            'login' => fake()->userName(),
        ];
    }
}
