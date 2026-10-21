<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerJsonPackage;

use function fake;

/**
 * @extends Factory<ComposerJsonPackage>
 */
class ComposerJsonPackageFactory extends Factory
{
    /** @var class-string<ComposerJsonPackage> */
    protected $model = ComposerJsonPackage::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'composer_json_id' => ComposerJson::factory(),
            'name' => fake()->slug(),
            'constraint' => fake()->numerify('^#.#'),
            'section' => fake()->randomElement(PackageSection::cases()),
        ];
    }
}
