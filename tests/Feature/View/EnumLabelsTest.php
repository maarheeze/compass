<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Collector\Forge\DeploymentStatus;
use Maarheeze\Compass\Collector\Forge\SiteStatus;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\EnumLabels;

test('label key is the case name in the translation file of the enum', function (): void {
    $runType = fake()->randomElement(RunType::cases());

    $enumLabels = $this->app->make(EnumLabels::class);
    $labelKey = $enumLabels->labelKey($runType);

    expect($labelKey)
        ->toBe(sprintf('compass::run-type.%s', $runType->name));
});

test('label key refuses an enum without labels', function (): void {
    $enumLabels = $this->app->make(EnumLabels::class);

    $this->expectException(InvalidArgumentException::class);

    $enumLabels->labelKey(fake()->randomElement(BadgeType::cases()));
});

test('label key has a translation', function (UnitEnum $value): void {
    $enumLabels = $this->app->make(EnumLabels::class);
    $labelKey = $enumLabels->labelKey($value);

    $hasTranslation = $this->app->make(Translator::class)->has($labelKey);

    expect($hasTranslation)
        ->toBeTrue();
})->with(array_merge(
    AlertHistoryEntryType::cases(),
    AlertStatus::cases(),
    AlertType::cases(),
    ClientStatus::cases(),
    ConditionState::cases(),
    DeploymentStatus::cases(),
    PackageSection::cases(),
    RepositoryKind::cases(),
    RepositoryVisibility::cases(),
    RunStatus::cases(),
    RunType::cases(),
    SiteStatus::cases(),
    [
        DeploymentState::BEHIND,
        DeploymentState::DEPLOYING,
        DeploymentState::DEPLOYMENT_OVERDUE,
        DeploymentState::UP_TO_DATE,
        MissingState::IGNORED,
        MissingState::MISSING,
    ],
));
