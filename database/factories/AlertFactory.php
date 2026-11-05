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
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\User;
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
            AlertType::COMPOSER_LIBRARY_WITH_LOCK => $alertFactoryWithState->composerLibraryWithLock(),
            AlertType::COMPOSER_MINIMUM_STABILITY_UNSTABLE =>
                $alertFactoryWithState->composerMinimumStabilityUnstable(),
            AlertType::COMPOSER_PROJECT_WITHOUT_LOCK => $alertFactoryWithState->composerProjectWithoutLock(),
            AlertType::COMPOSER_TYPE_MISSING => $alertFactoryWithState->composerTypeMissing(),
            AlertType::COMPOSER_TYPE_UNKNOWN => $alertFactoryWithState->composerTypeUnknown(),
            AlertType::DEPENDABOT_COMPOSER_MISSING => $alertFactoryWithState->dependabotComposerMissing(),
            AlertType::DEPENDABOT_NPM_MISSING => $alertFactoryWithState->dependabotNpmMissing(),
            AlertType::REPOSITORY_MISSING => $alertFactoryWithState->repositoryMissing(),
            AlertType::SITE_BEHIND => $alertFactoryWithState->siteBehind(),
            AlertType::SITE_CLIENT_INCOMPATIBLE => $alertFactoryWithState->siteClientIncompatible(),
            AlertType::SITE_CLIENT_UNAUTHORIZED => $alertFactoryWithState->siteClientUnauthorized(),
            AlertType::SITE_DEBUG_ENABLED => $alertFactoryWithState->siteDebugEnabled(),
            AlertType::SITE_DEPLOYMENT_OVERDUE => $alertFactoryWithState->siteDeploymentOverdue(),
            AlertType::SITE_ENDPOINT_DOWN => $alertFactoryWithState->siteEndpointDown(),
            AlertType::SITE_MISSING => $alertFactoryWithState->siteMissing(),
            AlertType::SITE_NOT_IN_PRODUCTION => $alertFactoryWithState->siteNotInProduction(),
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

    public function composerLibraryWithLock(): static
    {
        return $this->state(['type' => AlertType::COMPOSER_LIBRARY_WITH_LOCK])->for(Repository::factory(), 'subject');
    }

    public function composerMinimumStabilityUnstable(): static
    {
        return $this
            ->state(['type' => AlertType::COMPOSER_MINIMUM_STABILITY_UNSTABLE])
            ->for(Repository::factory(), 'subject');
    }

    public function composerProjectWithoutLock(): static
    {
        return $this
            ->state(['type' => AlertType::COMPOSER_PROJECT_WITHOUT_LOCK])
            ->for(Repository::factory(), 'subject');
    }

    public function composerTypeMissing(): static
    {
        return $this->state(['type' => AlertType::COMPOSER_TYPE_MISSING])->for(Repository::factory(), 'subject');
    }

    public function composerTypeUnknown(): static
    {
        return $this->state(['type' => AlertType::COMPOSER_TYPE_UNKNOWN])->for(Repository::factory(), 'subject');
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'assignee_id' => fake()->optional()->passthrough(User::factory()),
            'is_muted' => false,
        ];
    }

    public function dependabotComposerMissing(): static
    {
        return $this->state(['type' => AlertType::DEPENDABOT_COMPOSER_MISSING])->for(Repository::factory(), 'subject');
    }

    public function dependabotNpmMissing(): static
    {
        return $this->state(['type' => AlertType::DEPENDABOT_NPM_MISSING])->for(Repository::factory(), 'subject');
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

    public function muted(): static
    {
        return $this->state(['is_muted' => true]);
    }

    public function repositoryMissing(): static
    {
        return $this->state(['type' => AlertType::REPOSITORY_MISSING])->for(Repository::factory(), 'subject');
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

    public function siteClientIncompatible(): static
    {
        return $this->state(['type' => AlertType::SITE_CLIENT_INCOMPATIBLE])->for(SiteReport::factory(), 'subject');
    }

    public function siteClientUnauthorized(): static
    {
        return $this->state(['type' => AlertType::SITE_CLIENT_UNAUTHORIZED])->for(SiteReport::factory(), 'subject');
    }

    public function siteDebugEnabled(): static
    {
        return $this->state(['type' => AlertType::SITE_DEBUG_ENABLED])->for(SiteReport::factory(), 'subject');
    }

    public function siteDeploymentOverdue(): static
    {
        return $this->state(['type' => AlertType::SITE_DEPLOYMENT_OVERDUE])->for(Site::factory(), 'subject');
    }

    public function siteEndpointDown(): static
    {
        return $this->state(['type' => AlertType::SITE_ENDPOINT_DOWN])->for(SiteEndpoint::factory(), 'subject');
    }

    public function siteMissing(): static
    {
        return $this->state(['type' => AlertType::SITE_MISSING])->for(Site::factory(), 'subject');
    }

    public function siteNotInProduction(): static
    {
        return $this->state(['type' => AlertType::SITE_NOT_IN_PRODUCTION])->for(SiteReport::factory(), 'subject');
    }
}
