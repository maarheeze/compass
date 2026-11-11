<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertTypeGroup;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Analyzer\Deployment\DeploymentState;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\View\LabelledEnum;

test('label key has a translation', function (LabelledEnum $labelledEnum): void {
    $labelKey = $labelledEnum->labelKey();

    $hasTranslation = $this->app->make(Translator::class)->has($labelKey);

    expect($hasTranslation)
        ->toBeTrue();
})->with(array_merge(
    AlertHistoryEntryType::cases(),
    AlertStatus::cases(),
    AlertTypeGroup::cases(),
    CollectorType::cases(),
    ConditionState::cases(),
    DeploymentState::cases(),
    DeploymentStatus::cases(),
    MissingState::cases(),
    NotificationChannel::cases(),
    PackageSection::cases(),
    RepositoryKind::cases(),
    RepositoryVisibility::cases(),
    RunStatus::cases(),
    SiteReportStatus::cases(),
    TaskType::cases(),
));
