<?php

declare(strict_types=1);

use Illuminate\Contracts\Translation\Translator;
use Maarheeze\Compass\Alerts\AlertCheckState;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertLevel;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertTypeGroup;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Analyser\Composer\RepositoryKind;
use Maarheeze\Compass\Analyser\Deployment\DeploymentState;
use Maarheeze\Compass\Analyser\Processes\QueueWorkerHealth;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealth;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Collector\Issues\IssueType;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Collector\Repositories\RepositoryVisibility;
use Maarheeze\Compass\Collector\RunResultStatus;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainStatus;
use Maarheeze\Compass\Collector\SiteDomains\SiteDomainType;
use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Collector\SiteProcesses\QueueWorkerState;
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
    AlertCheckState::cases(),
    AlertHistoryEntryType::cases(),
    AlertLevel::cases(),
    AlertStatus::cases(),
    AlertTypeGroup::cases(),
    CollectorType::cases(),
    ConditionState::cases(),
    DeploymentState::cases(),
    DeploymentStatus::cases(),
    IssueType::cases(),
    MissingState::cases(),
    NotificationChannel::cases(),
    PackageSection::cases(),
    QueueWorkerHealth::cases(),
    QueueWorkerState::cases(),
    RepositoryKind::cases(),
    RepositoryVisibility::cases(),
    RunResultStatus::cases(),
    RunStatus::cases(),
    SchedulerHealth::cases(),
    SiteDomainStatus::cases(),
    SiteDomainType::cases(),
    SiteEndpointState::cases(),
    SiteReportStatus::cases(),
    TaskType::cases(),
));
