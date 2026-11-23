<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\RunResultStatus;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunResult;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Webmozart\Assert\Assert;

use function fake;

/**
 * @extends Factory<RunResult>
 */
class RunResultFactory extends Factory
{
    /** @var class-string<RunResult> */
    protected $model = RunResult::class;

    public function configure(): static
    {
        $runResultFactoryWithStatus = fake()->randomElement([
            $this->failed(),
            $this->succeeded(),
        ]);
        Assert::isInstanceOf($runResultFactoryWithStatus, static::class);

        $subjectFactory = fake()->randomElement([
            Repository::factory(),
            Site::factory(),
            SiteEndpoint::factory(),
        ]);
        Assert::isInstanceOf($subjectFactory, Factory::class);

        return $runResultFactoryWithStatus->for($subjectFactory, 'subject');
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'run_id' => Run::factory(),
        ];
    }

    public function failed(): static
    {
        return $this->state([
            'status' => RunResultStatus::FAILED,
            'messages' => [fake()->sentence()],
        ]);
    }

    public function succeeded(): static
    {
        return $this->state([
            'status' => RunResultStatus::SUCCEEDED,
            'messages' => [],
        ]);
    }
}
