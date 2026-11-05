<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;

use function fake;
use function sprintf;

/**
 * @extends Factory<SiteReport>
 */
class SiteReportFactory extends Factory
{
    /** @var class-string<SiteReport> */
    protected $model = SiteReport::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_id' => Site::factory(),
            'source' => fake()->slug(),
            'status' => fake()->randomElement(SiteReportStatus::cases()),
            'app_debug' => fake()->optional()->boolean(),
            'app_env' => fake()->optional()->word(),
            'app_name' => fake()->optional()->company(),
            'key' => fake()->optional()->sha1(),
            'path' => fake()->optional()->passthrough(sprintf('/%s', fake()->slug())),
            'collected_at' => fake()->dateTime(),
        ];
    }
}
