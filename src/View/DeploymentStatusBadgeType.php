<?php

declare(strict_types=1);

namespace Maarheeze\Compass\View;

use Maarheeze\Compass\Collector\Sites\DeploymentStatus;

readonly class DeploymentStatusBadgeType
{
    public function badgeType(DeploymentStatus $deploymentStatus): BadgeType
    {
        return match ($deploymentStatus) {
            DeploymentStatus::CANCELLED => BadgeType::NEUTRAL,
            DeploymentStatus::FAILED => BadgeType::DANGER,
            DeploymentStatus::QUEUED, DeploymentStatus::RUNNING => BadgeType::INFO,
            DeploymentStatus::SUCCEEDED => BadgeType::SUCCESS,
        };
    }
}
