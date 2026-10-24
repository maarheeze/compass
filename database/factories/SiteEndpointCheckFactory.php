<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpointCheck;

use function fake;
use function sprintf;

/**
 * @extends Factory<SiteEndpointCheck>
 */
class SiteEndpointCheckFactory extends Factory
{
    /** @var class-string<SiteEndpointCheck> */
    protected $model = SiteEndpointCheck::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'path' => sprintf('/%s', fake()->slug()),
            'url' => fake()->url(),
            'status_code' => fake()->optional()->numberBetween(100, 599),
        ];
    }
}
