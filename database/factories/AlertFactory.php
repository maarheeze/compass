<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\AlertCondition;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Webmozart\Assert\Assert;

use function fake;

/**
 * @extends Factory<Alert>
 */
class AlertFactory extends Factory
{
    /** @var class-string<Alert> */
    protected $model = Alert::class;

    public function configure(): static
    {
        $condition = fake()->randomElement(AlertCondition::cases());
        Assert::isInstanceOf($condition, AlertCondition::class);

        $alertFactoryWithCondition = match ($condition) {
            AlertCondition::GONE => $this->gone(),
            AlertCondition::PRESENT => $this->present(),
        };

        $type = fake()->randomElement(AlertType::cases());
        Assert::isInstanceOf($type, AlertType::class);

        return match ($type) {
            AlertType::DEBUG_ENABLED => $alertFactoryWithCondition->debugEnabled(),
            AlertType::DOWN_SITE_ENDPOINT => $alertFactoryWithCondition->downSiteEndpoint(),
            AlertType::INCOMPATIBLE_CLIENT => $alertFactoryWithCondition->incompatibleClient(),
            AlertType::MISSING_REPOSITORY => $alertFactoryWithCondition->missingRepository(),
            AlertType::NON_PRODUCTION => $alertFactoryWithCondition->nonProduction(),
            AlertType::UNAUTHORIZED_CLIENT => $alertFactoryWithCondition->unauthorizedClient(),
        };
    }

    public function debugEnabled(): static
    {
        return $this->state(['type' => AlertType::DEBUG_ENABLED])->for(Site::factory(), 'subject');
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'status' => AlertStatus::OPEN,
        ];
    }

    public function downSiteEndpoint(): static
    {
        return $this->state(['type' => AlertType::DOWN_SITE_ENDPOINT])->for(SiteEndpoint::factory(), 'subject');
    }

    public function gone(): static
    {
        return $this->state([
            'condition' => AlertCondition::GONE,
            'gone_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
        ]);
    }

    public function incompatibleClient(): static
    {
        return $this->state(['type' => AlertType::INCOMPATIBLE_CLIENT])->for(Site::factory(), 'subject');
    }

    public function missingRepository(): static
    {
        return $this->state(['type' => AlertType::MISSING_REPOSITORY])->for(Repository::factory(), 'subject');
    }

    public function nonProduction(): static
    {
        return $this->state(['type' => AlertType::NON_PRODUCTION])->for(Site::factory(), 'subject');
    }

    public function present(): static
    {
        return $this->state([
            'condition' => AlertCondition::PRESENT,
            'gone_at' => null,
        ]);
    }

    public function unauthorizedClient(): static
    {
        return $this->state(['type' => AlertType::UNAUTHORIZED_CLIENT])->for(Site::factory(), 'subject');
    }
}
