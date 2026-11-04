<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

use function fake;
use function sprintf;

/**
 * @extends Factory<SiteEndpoint>
 */
class SiteEndpointFactory extends Factory
{
    /** @var class-string<SiteEndpoint> */
    protected $model = SiteEndpoint::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_report_id' => SiteReport::factory(),
            'path' => sprintf('/%s', fake()->unique()->slug()),
        ];
    }
}
