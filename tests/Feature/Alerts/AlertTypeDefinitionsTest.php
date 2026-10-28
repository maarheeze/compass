<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeDefinitions;
use Maarheeze\Compass\Alerts\Types\DebugEnabledAlert;
use Maarheeze\Compass\Alerts\Types\DownSiteEndpointAlert;
use Maarheeze\Compass\Alerts\Types\NonProductionAlert;

test('definition of debug enabled is the debug enabled alert', function (): void {
    $alertTypeDefinitions = $this->app->make(AlertTypeDefinitions::class);
    $alertTypeDefinition = $alertTypeDefinitions->definition(AlertType::DEBUG_ENABLED);

    expect($alertTypeDefinition)
        ->toBeInstanceOf(DebugEnabledAlert::class);
});

test('definition of a down site endpoint is the down site endpoint alert', function (): void {
    $alertTypeDefinitions = $this->app->make(AlertTypeDefinitions::class);
    $alertTypeDefinition = $alertTypeDefinitions->definition(AlertType::DOWN_SITE_ENDPOINT);

    expect($alertTypeDefinition)
        ->toBeInstanceOf(DownSiteEndpointAlert::class);
});

test('definition of non production is the non production alert', function (): void {
    $alertTypeDefinitions = $this->app->make(AlertTypeDefinitions::class);
    $alertTypeDefinition = $alertTypeDefinitions->definition(AlertType::NON_PRODUCTION);

    expect($alertTypeDefinition)
        ->toBeInstanceOf(NonProductionAlert::class);
});
