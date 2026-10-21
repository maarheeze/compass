<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Forge\DeploymentStatus;
use Maarheeze\Compass\Collector\Forge\SiteStatus;
use Maarheeze\Compass\Models\Site;

use function fake;

/**
 * @extends Factory<Site>
 */
class SiteFactory extends Factory
{
    /** @var class-string<Site> */
    protected $model = Site::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'forge_id' => fake()->unique()->numerify('#######'),
            'name' => fake()->domainName(),
            'url' => fake()->url(),
            'status' => fake()->randomElement(SiteStatus::cases()),
            'php_version' => fake()->optional()->numerify('php8#'),
            'repository_url' => fake()->optional()->url(),
            'repository_branch' => fake()->optional()->word(),
            'server_name' => fake()->slug(),
            'server_slug' => fake()->slug(),
            'server_ip_address' => fake()->optional()->ipv4(),
            'deployment_status' => fake()->optional()->randomElement(DeploymentStatus::cases()),
            'deployed_commit_hash' => fake()->optional()->sha1(),
            'deployed_commit_message' => fake()->optional()->sentence(),
            'deployment_started_at' => fake()->optional()->dateTimeBetween('-2 months', '-1 month'),
            'deployment_ended_at' => fake()->optional()->dateTimeBetween('-1 month'),
            'collection_run_id' => CollectionRunId::generate(),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
