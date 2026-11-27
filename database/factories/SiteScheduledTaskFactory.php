<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\SiteScheduledTask;

use function fake;
use function sprintf;

/**
 * @extends Factory<SiteScheduledTask>
 */
class SiteScheduledTaskFactory extends Factory
{
    /** @var class-string<SiteScheduledTask> */
    protected $model = SiteScheduledTask::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'site_report_id' => SiteReport::factory(),
            'command' => sprintf('php artisan %s', fake()->slug()),
            'expression' => sprintf('%d * * * *', fake()->numberBetween(0, 59)),
        ];
    }
}
