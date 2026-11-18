<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertType;
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
use Maarheeze\Compass\Alerts\Types\SiteReportUnauthorised;

test('level is the level of the alert type', function (AlertType $alertType, AlertLevel $alertLevel): void {
    $level = $alertType->level();

    expect($level)
        ->toBe($alertLevel);
})->with([
    [new ComposerLibraryWithLock(), AlertLevel::WARNING],
    [new ComposerMinimumStabilityUnstable(), AlertLevel::WARNING],
    [new ComposerProjectWithoutLock(), AlertLevel::WARNING],
    [new ComposerTypeMissing(), AlertLevel::WARNING],
    [new ComposerTypeUnknown(), AlertLevel::WARNING],
    [new DependabotComposerMissing(), AlertLevel::WARNING],
    [new DependabotNpmMissing(), AlertLevel::WARNING],
    [new RepositoryMissing(), AlertLevel::NOTICE],
    [new SiteBehind(), AlertLevel::NOTICE],
    [new SiteDebugEnabled(), AlertLevel::CRITICAL],
    [new SiteDeploymentOverdue(), AlertLevel::NOTICE],
    [new SiteEndpointDown(), AlertLevel::CRITICAL],
    [new SiteMissing(), AlertLevel::NOTICE],
    [new SiteNotInProduction(), AlertLevel::WARNING],
    [new SiteReportIncompatible(), AlertLevel::WARNING],
    [new SiteReportUnauthorised(), AlertLevel::WARNING],
]);
