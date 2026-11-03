<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use InvalidArgumentException;
use Maarheeze\Compass\Alerts\AlertHistoryEntryType;
use Maarheeze\Compass\Alerts\AlertStatus;
use Maarheeze\Compass\Alerts\AlertType;
use Maarheeze\Compass\Alerts\AlertTypeGroup;
use Maarheeze\Compass\Alerts\ConditionState;
use Maarheeze\Compass\Alerts\NotificationChannel;
use Maarheeze\Compass\Analyzer\Composer\RepositoryKind;
use Maarheeze\Compass\Analyzer\Forge\DeploymentState;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Collector\GitHub\RepositoryVisibility;
use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use UnitEnum;

use function sprintf;

readonly class EnumLabels
{
    public function labelKey(UnitEnum $value): string
    {
        $translationFile = match ($value::class) {
            AlertHistoryEntryType::class => 'alert-history-entry-type',
            AlertStatus::class => 'alert-status',
            AlertType::class => 'alert-type',
            AlertTypeGroup::class => 'alert-type-group',
            ClientStatus::class => 'client-status',
            ConditionState::class => 'condition-state',
            DeploymentState::class => 'deployment-state',
            DeploymentStatus::class => 'deployment-status',
            MissingState::class => 'missing-state',
            NotificationChannel::class => 'notification-channel',
            PackageSection::class => 'package-section',
            RepositoryKind::class => 'repository-kind',
            RepositoryVisibility::class => 'repository-visibility',
            RunStatus::class => 'run-status',
            RunType::class => 'run-type',
            default => throw new InvalidArgumentException(),
        };

        return sprintf('compass::%s.%s', $translationFile, $value->name);
    }
}
