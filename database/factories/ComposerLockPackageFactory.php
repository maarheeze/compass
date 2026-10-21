<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\ComposerLockPackage;

use function fake;

/**
 * @extends Factory<ComposerLockPackage>
 */
class ComposerLockPackageFactory extends Factory
{
    /** @var class-string<ComposerLockPackage> */
    protected $model = ComposerLockPackage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'composer_lock_id' => ComposerLock::factory(),
            'name' => fake()->slug(),
            'version' => fake()->numerify('v#.#.#'),
            'section' => fake()->randomElement(PackageSection::cases()),
        ];
    }
}
