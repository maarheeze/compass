<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Models\NotificationSubscription;
use Maarheeze\Compass\Models\User;

use function fake;

/**
 * @extends Factory<NotificationSubscription>
 */
class NotificationSubscriptionFactory extends Factory
{
    /** @var class-string<NotificationSubscription> */
    protected $model = NotificationSubscription::class;

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alert_type' => fake()->randomElement(AlertType::cases()),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'user_id' => User::factory(),
        ];
    }
}
