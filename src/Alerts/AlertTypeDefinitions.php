<?php

declare(strict_types=1);

namespace Maarheeze\Compass\Alerts;

use Illuminate\Contracts\Container\Container;
use Maarheeze\Compass\Alerts\Types\DownSiteEndpointAlert;

readonly class AlertTypeDefinitions
{
    public function __construct(
        private Container $container,
    ) {
    }

    public function definition(AlertType $alertType): AlertTypeDefinition
    {
        $definitionClass = match ($alertType) {
            AlertType::DOWN_SITE_ENDPOINT => DownSiteEndpointAlert::class,
        };

        return $this->container->make($definitionClass);
    }
}
