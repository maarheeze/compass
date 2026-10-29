<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
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
        $status = fake()->randomElement(AlertStatus::cases());
        Assert::isInstanceOf($status, AlertStatus::class);
        $condition = fake()->randomElement(ConditionState::cases());
        Assert::isInstanceOf($condition, ConditionState::class);

        $alertFactoryWithState = match ($status) {
            AlertStatus::DONE => $this->done(),
            AlertStatus::OPEN => match ($condition) {
                ConditionState::ACTIVE => $this->active(),
                ConditionState::RESOLVED => $this->resolved(),
            },
        };

        $type = fake()->randomElement(AlertType::cases());
        Assert::isInstanceOf($type, AlertType::class);

        return match ($type) {
            AlertType::DEBUG_ENABLED => $alertFactoryWithState->debugEnabled(),
            AlertType::DOWN_SITE_ENDPOINT => $alertFactoryWithState->downSiteEndpoint(),
            AlertType::INCOMPATIBLE_CLIENT => $alertFactoryWithState->incompatibleClient(),
            AlertType::LIBRARY_WITH_LOCK => $alertFactoryWithState->libraryWithLock(),
            AlertType::MISSING_COMPOSER_TYPE => $alertFactoryWithState->missingComposerType(),
            AlertType::MISSING_REPOSITORY => $alertFactoryWithState->missingRepository(),
            AlertType::MISSING_SITE => $alertFactoryWithState->missingSite(),
            AlertType::NON_PRODUCTION => $alertFactoryWithState->nonProduction(),
            AlertType::OVERDUE_DEPLOYMENT => $alertFactoryWithState->overdueDeployment(),
            AlertType::PROJECT_WITHOUT_LOCK => $alertFactoryWithState->projectWithoutLock(),
            AlertType::SITE_BEHIND => $alertFactoryWithState->siteBehind(),
            AlertType::UNAUTHORIZED_CLIENT => $alertFactoryWithState->unauthorizedClient(),
            AlertType::UNKNOWN_COMPOSER_TYPE => $alertFactoryWithState->unknownComposerType(),
        };
    }

    public function active(): static
    {
        return $this->state([
            'condition' => ConditionState::ACTIVE,
            'resolved_at' => null,
            'status' => AlertStatus::OPEN,
            'done_at' => null,
        ]);
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
        return [];
    }

    public function done(): static
    {
        return $this->state([
            'condition' => ConditionState::RESOLVED,
            'resolved_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'status' => AlertStatus::DONE,
            'done_at' => fake()->dateTimeBetween('-1 month'),
        ]);
    }

    public function downSiteEndpoint(): static
    {
        return $this->state(['type' => AlertType::DOWN_SITE_ENDPOINT])->for(SiteEndpoint::factory(), 'subject');
    }

    public function incompatibleClient(): static
    {
        return $this->state(['type' => AlertType::INCOMPATIBLE_CLIENT])->for(Site::factory(), 'subject');
    }

    public function libraryWithLock(): static
    {
        return $this->state(['type' => AlertType::LIBRARY_WITH_LOCK])->for(Repository::factory(), 'subject');
    }

    public function missingComposerType(): static
    {
        return $this->state(['type' => AlertType::MISSING_COMPOSER_TYPE])->for(Repository::factory(), 'subject');
    }

    public function missingRepository(): static
    {
        return $this->state(['type' => AlertType::MISSING_REPOSITORY])->for(Repository::factory(), 'subject');
    }

    public function missingSite(): static
    {
        return $this->state(['type' => AlertType::MISSING_SITE])->for(Site::factory(), 'subject');
    }

    public function muted(): static
    {
        return $this->state(['muted_until' => fake()->dateTimeBetween('+1 minute', '+1 hour')]);
    }

    public function nonProduction(): static
    {
        return $this->state(['type' => AlertType::NON_PRODUCTION])->for(Site::factory(), 'subject');
    }

    public function overdueDeployment(): static
    {
        return $this->state(['type' => AlertType::OVERDUE_DEPLOYMENT])->for(Site::factory(), 'subject');
    }

    public function projectWithoutLock(): static
    {
        return $this->state(['type' => AlertType::PROJECT_WITHOUT_LOCK])->for(Repository::factory(), 'subject');
    }

    public function resolved(): static
    {
        return $this->state([
            'condition' => ConditionState::RESOLVED,
            'resolved_at' => fake()->dateTimeBetween('-2 months', '-1 month'),
            'status' => AlertStatus::OPEN,
            'done_at' => null,
        ]);
    }

    public function siteBehind(): static
    {
        return $this->state(['type' => AlertType::SITE_BEHIND])->for(Site::factory(), 'subject');
    }

    public function unauthorizedClient(): static
    {
        return $this->state(['type' => AlertType::UNAUTHORIZED_CLIENT])->for(Site::factory(), 'subject');
    }

    public function unknownComposerType(): static
    {
        return $this->state(['type' => AlertType::UNKNOWN_COMPOSER_TYPE])->for(Repository::factory(), 'subject');
    }
}
