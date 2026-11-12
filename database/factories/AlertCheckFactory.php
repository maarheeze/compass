<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\AlertType;
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
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Webmozart\Assert\Assert;

use function fake;

/**
 * @extends Factory<AlertCheck>
 */
class AlertCheckFactory extends Factory
{
    /** @var class-string<AlertCheck> */
    protected $model = AlertCheck::class;

    public function configure(): static
    {
        $subjectFactory = fake()->randomElement([
            Repository::factory(),
            Site::factory(),
            SiteEndpoint::factory(),
            SiteReport::factory(),
        ]);
        Assert::isInstanceOf($subjectFactory, Factory::class);

        return $this->for($subjectFactory, 'subject');
    }

    /**
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        $alertType = fake()->randomElement([
            new ComposerLibraryWithLock(),
            new ComposerMinimumStabilityUnstable(),
            new ComposerProjectWithoutLock(),
            new ComposerTypeMissing(),
            new ComposerTypeUnknown(),
            new DependabotComposerMissing(),
            new DependabotNpmMissing(),
            new RepositoryMissing(),
            new SiteBehind(),
            new SiteDebugEnabled(),
            new SiteDeploymentOverdue(),
            new SiteEndpointDown(),
            new SiteMissing(),
            new SiteNotInProduction(),
            new SiteReportIncompatible(),
            new SiteReportUnauthorized(),
        ]);
        Assert::isInstanceOf($alertType, AlertType::class);

        return [
            'alert_type_identifier' => $alertType->identifier(),
            'condition' => fake()->randomElement(ConditionState::cases()),
            'observed_at' => fake()->dateTimeBetween('-1 month'),
        ];
    }
}
