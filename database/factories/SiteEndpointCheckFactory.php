<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Http\EndpointPath;
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
            'path' => new EndpointPath(sprintf('/%s', fake()->slug())),
            'url' => fake()->url(),
            'status_code' => fake()->optional()->numberBetween(100, 599),
            'is_successful' => fake()->boolean(),
        ];
    }
}
