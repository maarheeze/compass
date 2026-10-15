<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Attributes\UseModel;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;
use Maarheeze\Compass\Models\User;

use function fake;

/**
 * @extends Factory<User>
 */
#[UseModel(User::class)]
class UserFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $nickname = fake()->unique()->userName();

        return [
            'github_id' => (string) fake()->unique()->numberBetween(1, 999999),
            'nickname' => $nickname,
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'avatar_url' => 'https://avatars.githubusercontent.com/u/' . $nickname,
            'github_token' => 'gho_' . Str::random(36),
            'remember_token' => Str::random(10),
        ];
    }
}
