<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Contracts\Container\Container;
use Maarheeze\Compass\Alerts\Types\DebugEnabledAlert;
use Maarheeze\Compass\Alerts\Types\DownSiteEndpointAlert;
use Maarheeze\Compass\Alerts\Types\IncompatibleClientAlert;
use Maarheeze\Compass\Alerts\Types\MissingRepositoryAlert;
use Maarheeze\Compass\Alerts\Types\NonProductionAlert;
use Maarheeze\Compass\Alerts\Types\UnauthorizedClientAlert;

readonly class AlertTypeDefinitions
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function definition(AlertType $alertType): AlertTypeDefinition
    {
        $definitionClass = match ($alertType) {
            AlertType::DEBUG_ENABLED => DebugEnabledAlert::class,
            AlertType::DOWN_SITE_ENDPOINT => DownSiteEndpointAlert::class,
            AlertType::INCOMPATIBLE_CLIENT => IncompatibleClientAlert::class,
            AlertType::MISSING_REPOSITORY => MissingRepositoryAlert::class,
            AlertType::NON_PRODUCTION => NonProductionAlert::class,
            AlertType::UNAUTHORIZED_CLIENT => UnauthorizedClientAlert::class,
        };

        return $this->container->make($definitionClass);
    }
}
