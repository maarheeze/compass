<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunFailure;

use function fake;

/**
 * @extends Factory<RunFailure>
 */
class RunFailureFactory extends Factory
{
    /** @var class-string<RunFailure> */
    protected $model = RunFailure::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
            'message' => fake()->sentence(),
        ];
    }
}
