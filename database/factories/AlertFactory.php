<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\Types\ComposerLibraryWithLock;
use Maarheeze\Compass\Alerts\Types\ComposerMinimumStabilityUnstable;
use Maarheeze\Compass\Alerts\Types\ComposerProjectWithoutLock;
use Maarheeze\Compass\Alerts\Types\ComposerTypeMissing;
use Maarheeze\Compass\Alerts\Types\ComposerTypeUnknown;
use Maarheeze\Compass\Alerts\Types\DependabotComposerMissing;
use Maarheeze\Compass\Alerts\Types\DependabotNpmMissing;
use Maarheeze\Compass\Alerts\Types\RepositoryMissing;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Alerts\Types\SiteDebugEnabled;
use Maarheeze\Compass\Alerts\Types\SiteDeploymentOverdue;
use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\Alerts\Types\SiteMissing;
use Maarheeze\Compass\Alerts\Types\SiteNotInProduction;
use Maarheeze\Compass\Alerts\Types\SiteReportIncompatible;
use Maarheeze\Compass\Alerts\Types\SiteReportUnauthorized;
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
        $alertFactoryWithState = fake()->randomElement([
            $this->active(),
            $this->done(),
            $this->resolved(),
        ]);
        Assert::isInstanceOf($alertFactoryWithState, static::class);

        $alertFactoryWithType = fake()->randomElement([
            $alertFactoryWithState->composerLibraryWithLock(),
            $alertFactoryWithState->composerMinimumStabilityUnstable(),
            $alertFactoryWithState->composerProjectWithoutLock(),
            $alertFactoryWithState->composerTypeMissing(),
            $alertFactoryWithState->composerTypeUnknown(),
            $alertFactoryWithState->dependabotComposerMissing(),
            $alertFactoryWithState->dependabotNpmMissing(),
            $alertFactoryWithState->repositoryMissing(),
            $alertFactoryWithState->siteBehind(),
            $alertFactoryWithState->siteClientIncompatible(),
            $alertFactoryWithState->siteClientUnauthorized(),
            $alertFactoryWithState->siteDebugEnabled(),
            $alertFactoryWithState->siteDeploymentOverdue(),
            $alertFactoryWithState->siteEndpointDown(),
            $alertFactoryWithState->siteMissing(),
            $alertFactoryWithState->siteNotInProduction(),
        ]);
        Assert::isInstanceOf($alertFactoryWithType, static::class);

        return $alertFactoryWithType;
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
        return $this
            ->state(['alert_type_identifier' => new ComposerLibraryWithLock()->identifier()])
            ->for(Repository::factory(), 'subject');
    }

    public function composerMinimumStabilityUnstable(): static
    {
        return $this
            ->state(['alert_type_identifier' => new ComposerMinimumStabilityUnstable()->identifier()])
            ->for(Repository::factory(), 'subject');
    }

    public function composerProjectWithoutLock(): static
    {
        return $this
            ->state(['alert_type_identifier' => new ComposerProjectWithoutLock()->identifier()])
            ->for(Repository::factory(), 'subject');
    }

    public function composerTypeMissing(): static
    {
        return $this
            ->state(['alert_type_identifier' => new ComposerTypeMissing()->identifier()])
            ->for(Repository::factory(), 'subject');
    }

    public function composerTypeUnknown(): static
    {
        return $this
            ->state(['alert_type_identifier' => new ComposerTypeUnknown()->identifier()])
            ->for(Repository::factory(), 'subject');
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
        return $this
            ->state(['alert_type_identifier' => new DependabotComposerMissing()->identifier()])
            ->for(Repository::factory(), 'subject');
    }

    public function dependabotNpmMissing(): static
    {
        return $this
            ->state(['alert_type_identifier' => new DependabotNpmMissing()->identifier()])
            ->for(Repository::factory(), 'subject');
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
        return $this
            ->state(['alert_type_identifier' => new RepositoryMissing()->identifier()])
            ->for(Repository::factory(), 'subject');
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
        return $this
            ->state(['alert_type_identifier' => new SiteBehind()->identifier()])
            ->for(Site::factory(), 'subject');
    }

    public function siteClientIncompatible(): static
    {
        return $this
            ->state(['alert_type_identifier' => new SiteReportIncompatible()->identifier()])
            ->for(SiteReport::factory(), 'subject');
    }

    public function siteClientUnauthorized(): static
    {
        return $this
            ->state(['alert_type_identifier' => new SiteReportUnauthorized()->identifier()])
            ->for(SiteReport::factory(), 'subject');
    }

    public function siteDebugEnabled(): static
    {
        return $this
            ->state(['alert_type_identifier' => new SiteDebugEnabled()->identifier()])
            ->for(SiteReport::factory(), 'subject');
    }

    public function siteDeploymentOverdue(): static
    {
        return $this
            ->state(['alert_type_identifier' => new SiteDeploymentOverdue()->identifier()])
            ->for(Site::factory(), 'subject');
    }

    public function siteEndpointDown(): static
    {
        return $this
            ->state(['alert_type_identifier' => new SiteEndpointDown()->identifier()])
            ->for(SiteEndpoint::factory(), 'subject');
    }

    public function siteMissing(): static
    {
        return $this
            ->state(['alert_type_identifier' => new SiteMissing()->identifier()])
            ->for(Site::factory(), 'subject');
    }

    public function siteNotInProduction(): static
    {
        return $this
            ->state(['alert_type_identifier' => new SiteNotInProduction()->identifier()])
            ->for(SiteReport::factory(), 'subject');
    }
}
