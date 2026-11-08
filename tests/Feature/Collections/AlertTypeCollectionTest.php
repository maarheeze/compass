<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\AlertTypeGroup;
use Maarheeze\Compass\Alerts\Types\RepositoryMissing;
use Maarheeze\Compass\Alerts\Types\SiteBehind;
use Maarheeze\Compass\Alerts\Types\SiteMissing;
use Maarheeze\Compass\Collections\AlertTypeCollection;

test('alert type groups are the groups of the alert types in the order each first appears', function (): void {
    $alertTypes = new AlertTypeCollection([new SiteBehind(), new RepositoryMissing(), new SiteMissing()]);

    $alertTypeGroups = $alertTypes->alertTypeGroups();

    expect($alertTypeGroups)
        ->toBe([AlertTypeGroup::HOSTING_PROVIDER, AlertTypeGroup::CODE_HOST]);
});

test('of group keeps the alert types of the group', function (): void {
    $siteBehind = new SiteBehind();
    $siteMissing = new SiteMissing();
    $alertTypes = new AlertTypeCollection([$siteBehind, $siteMissing]);

    $alertTypesOfGroup = $alertTypes->ofGroup(AlertTypeGroup::HOSTING_PROVIDER);

    expect($alertTypesOfGroup->all())
        ->toBe([$siteBehind, $siteMissing]);
});

test('of group leaves out the alert types of another group', function (): void {
    $alertTypes = new AlertTypeCollection([new RepositoryMissing()]);

    $alertTypesOfGroup = $alertTypes->ofGroup(AlertTypeGroup::HOSTING_PROVIDER);

    expect($alertTypesOfGroup)
        ->toBeEmpty();
});
