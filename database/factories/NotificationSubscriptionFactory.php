<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;
use Maarheeze\Compass\Alerts\NotificationChannel;
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
            'alert_type_identifier' => fake()->randomElement([
                new ComposerLibraryWithLock()->identifier(),
                new ComposerMinimumStabilityUnstable()->identifier(),
                new ComposerProjectWithoutLock()->identifier(),
                new ComposerTypeMissing()->identifier(),
                new ComposerTypeUnknown()->identifier(),
                new DependabotComposerMissing()->identifier(),
                new DependabotNpmMissing()->identifier(),
                new RepositoryMissing()->identifier(),
                new SiteBehind()->identifier(),
                new SiteDebugEnabled()->identifier(),
                new SiteDeploymentOverdue()->identifier(),
                new SiteEndpointDown()->identifier(),
                new SiteMissing()->identifier(),
                new SiteNotInProduction()->identifier(),
                new SiteReportIncompatible()->identifier(),
                new SiteReportUnauthorized()->identifier(),
            ]),
            'channel' => fake()->randomElement(NotificationChannel::cases()),
            'user_id' => User::factory(),
        ];
    }
}
