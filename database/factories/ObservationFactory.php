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
            AlertType::SITE_ENDPOINT_DOWN => SiteEndpoint::factory(),
            AlertType::SITE_BEHIND,
            AlertType::SITE_CLIENT_INCOMPATIBLE,
            AlertType::SITE_CLIENT_UNAUTHORIZED,
            AlertType::SITE_DEBUG_ENABLED,
            AlertType::SITE_DEPLOYMENT_OVERDUE,
            AlertType::SITE_MISSING,
            AlertType::SITE_NOT_IN_PRODUCTION => Site::factory(),
            AlertType::COMPOSER_LIBRARY_WITH_LOCK,
            AlertType::COMPOSER_MINIMUM_STABILITY_UNSTABLE,
            AlertType::COMPOSER_PROJECT_WITHOUT_LOCK,
            AlertType::COMPOSER_TYPE_MISSING,
            AlertType::COMPOSER_TYPE_UNKNOWN,
            AlertType::DEPENDABOT_COMPOSER_MISSING,
            AlertType::DEPENDABOT_NPM_MISSING,
            AlertType::REPOSITORY_MISSING => Repository::factory(),
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
