<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;
use Maarheeze\Compass\Alerts\AlertTypeGroups;
use Maarheeze\Compass\Alerts\AlertTypes;
use Maarheeze\Compass\Alerts\GroupedAlertTypes;
use Maarheeze\Compass\Alerts\Types\RepositoryMissing;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Alerts\Types\SiteMissing;

test('grouped puts each registered alert type under its group', function (): void {
    $siteBehind = new SiteBehind();
    $siteMissing = new SiteMissing();
    $alertTypes = new AlertTypes();
    $alertTypes->register($siteBehind);
    $alertTypes->register($siteMissing);

    $alertTypeGroups = new AlertTypeGroups($alertTypes);
    $groupedAlertTypes = $alertTypeGroups->grouped();

    expect($groupedAlertTypes)
        ->toEqual([new GroupedAlertTypes(AlertTypeGroup::HOSTING_PROVIDER, [$siteBehind, $siteMissing])]);
});

test('grouped orders the groups by their first registered alert type', function (): void {
    $customAlertType = new readonly class implements AlertType {
        public function group(): AlertTypeGroup
        {
            return AlertTypeGroup::CUSTOM;
        }

        public function identifier(): string
        {
            return 'custom-alert-type';
        }

        public function translationKey(): string
        {
            return 'custom::alert-type.CUSTOM_ALERT_TYPE';
        }
    };
    $repositoryMissing = new RepositoryMissing();
    $alertTypes = new AlertTypes();
    $alertTypes->register($customAlertType);
    $alertTypes->register($repositoryMissing);

    $alertTypeGroups = new AlertTypeGroups($alertTypes);
    $groupedAlertTypes = $alertTypeGroups->grouped();

    expect($groupedAlertTypes)
        ->toEqual([
            new GroupedAlertTypes(AlertTypeGroup::CUSTOM, [$customAlertType]),
            new GroupedAlertTypes(AlertTypeGroup::CODE_HOST, [$repositoryMissing]),
        ]);
});

test('grouped leaves out a group without registered alert types', function (): void {
    $alertTypes = new AlertTypes();

    $alertTypeGroups = new AlertTypeGroups($alertTypes);
    $groupedAlertTypes = $alertTypeGroups->grouped();

    expect($groupedAlertTypes)
        ->toBeEmpty();
});
