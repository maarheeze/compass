<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Uuid\Uuid;

use function fake;

/**
 * @extends Factory<Run>
 */
class RunFactory extends Factory
{
    /** @var class-string<Run> */
    protected $model = Run::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'id' => Uuid::generate(),
            'source' => fake()->slug(),
            'collector' => fake()->randomElement(CollectorType::cases()),
            'status' => RunStatus::RUNNING,
            'batch_id' => fake()->uuid(),
            'finished_at' => null,
            'failed_job_count' => null,
        ];
    }
}
