<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Contracts\Container\Container;
use Maarheeze\Compass\Alerts\Types\DebugEnabledAlert;
use Maarheeze\Compass\Alerts\Types\DownSiteEndpointAlert;
use Maarheeze\Compass\Alerts\Types\NonProductionAlert;

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
            AlertType::NON_PRODUCTION => NonProductionAlert::class,
        };

        return $this->container->make($definitionClass);
    }
}
