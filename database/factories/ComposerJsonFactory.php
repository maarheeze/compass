<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;

use function fake;

/**
 * @extends Factory<ComposerJson>
 */
class ComposerJsonFactory extends Factory
{
    /** @var class-string<ComposerJson> */
    protected $model = ComposerJson::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repository_id' => Repository::factory(),
            'oid' => fake()->sha1(),
            'type' => fake()->optional()->word(),
            'php_constraint' => fake()->optional()->numerify('^#.#'),
        ];
    }
}
