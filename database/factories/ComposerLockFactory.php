<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\Repository;

use function fake;

/**
 * @extends Factory<ComposerLock>
 */
class ComposerLockFactory extends Factory
{
    /** @var class-string<ComposerLock> */
    protected $model = ComposerLock::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'repository_id' => Repository::factory(),
            'oid' => fake()->sha1(),
            'php_constraint' => fake()->optional()->numerify('^#.#'),
        ];
    }
}
