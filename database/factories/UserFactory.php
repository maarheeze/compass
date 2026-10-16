<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Maarheeze\Compass\Models\User;

use function fake;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    /** @var class-string<User> */
    protected $model = User::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'github_id' => (string) fake()->unique()->numberBetween(1, 999999),
            'nickname' => fake()->unique()->userName(),
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'avatar_url' => fake()->unique()->url(),
            'github_token' => 'gho_' . Str::random(36),
            'remember_token' => Str::random(10),
        ];
    }
}
