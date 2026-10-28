<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertHistoryEntry;
use Maarheeze\Compass\Models\User;
use Webmozart\Assert\Assert;

use function fake;

/**
 * @extends Factory<AlertHistoryEntry>
 */
class AlertHistoryEntryFactory extends Factory
{
    /** @var class-string<AlertHistoryEntry> */
    protected $model = AlertHistoryEntry::class;

    public function cleared(): static
    {
        return $this->state([
            'type' => AlertHistoryEntryType::CLEARED,
            'user_id' => null,
        ]);
    }

    public function configure(): static
    {
        $type = fake()->randomElement(AlertHistoryEntryType::cases());
        Assert::isInstanceOf($type, AlertHistoryEntryType::class);

        return match ($type) {
            AlertHistoryEntryType::CLEARED => $this->cleared(),
            AlertHistoryEntryType::MARKED_DONE => $this->markedDone(),
            AlertHistoryEntryType::OPENED => $this->opened(),
            AlertHistoryEntryType::RECURRED => $this->recurred(),
        };
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alert_id' => Alert::factory(),
            'occurred_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }

    public function markedDone(): static
    {
        return $this->state([
            'type' => AlertHistoryEntryType::MARKED_DONE,
            'user_id' => User::factory(),
        ]);
    }

    public function opened(): static
    {
        return $this->state([
            'type' => AlertHistoryEntryType::OPENED,
            'user_id' => null,
        ]);
    }

    public function recurred(): static
    {
        return $this->state([
            'type' => AlertHistoryEntryType::RECURRED,
            'user_id' => null,
        ]);
    }
}
