<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Uuid\Uuid;

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
            'source' => fake()->slug(),
            'forge_id' => fake()->unique()->numerify('#######'),
            'name' => fake()->domainName(),
            'url' => fake()->url(),
            'php_version' => fake()->optional()->numerify('php8#'),
            'repository_url' => fake()->optional()->url(),
            'repository_branch' => fake()->optional()->word(),
            'auto_deploy' => fake()->optional()->boolean(),
            'auto_deploy_grace_minutes' => fake()->numberBetween(1, 60),
            'server_name' => fake()->slug(),
            'server_ip_address' => fake()->optional()->ipv4(),
            'deployment_status' => fake()->optional()->randomElement(DeploymentStatus::cases()),
            'deployed_commit_hash' => fake()->optional()->sha1(),
            'deployment_started_at' => fake()->optional()->dateTimeBetween('-2 months', '-1 month'),
            'deployment_ended_at' => fake()->optional()->dateTimeBetween('-1 month'),
            'run_id' => Uuid::generate(),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
