<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Models\Observation;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Webmozart\Assert\Assert;

use function fake;

/**
 * @extends Factory<Observation>
 */
class ObservationFactory extends Factory
{
    /** @var class-string<Observation> */
    protected $model = Observation::class;

    public function configure(): static
    {
        $alertType = fake()->randomElement(AlertType::cases());
        Assert::isInstanceOf($alertType, AlertType::class);

        $subjectFactory = match ($alertType) {
            AlertType::DOWN_SITE_ENDPOINT => SiteEndpoint::factory(),
            AlertType::DEBUG_ENABLED,
            AlertType::INCOMPATIBLE_CLIENT,
            AlertType::MISSING_SITE,
            AlertType::NON_PRODUCTION,
            AlertType::OVERDUE_DEPLOYMENT,
            AlertType::SITE_BEHIND,
            AlertType::UNAUTHORIZED_CLIENT => Site::factory(),
            AlertType::LIBRARY_WITH_LOCK,
            AlertType::MISSING_COMPOSER_TYPE,
            AlertType::MISSING_REPOSITORY,
            AlertType::PROJECT_WITHOUT_LOCK,
            AlertType::UNKNOWN_COMPOSER_TYPE => Repository::factory(),
        };

        return $this
            ->state(['alert_type' => $alertType])
            ->for($subjectFactory, 'subject');
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'alert_id' => null,
            'condition' => fake()->randomElement(ConditionState::cases()),
            'observed_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
