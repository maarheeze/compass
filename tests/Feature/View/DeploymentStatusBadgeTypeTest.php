<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\DeploymentStatusBadgeType;

test('badge type follows the deployment status', function (DeploymentStatus $deploymentStatus, BadgeType $badgeType): void {
    $deploymentStatusBadgeType = new DeploymentStatusBadgeType();

    $statusBadgeType = $deploymentStatusBadgeType->badgeType($deploymentStatus);

    expect($statusBadgeType)
        ->toBe($badgeType);
})->with([
    'cancelled' => [DeploymentStatus::CANCELLED, BadgeType::NEUTRAL],
    'failed' => [DeploymentStatus::FAILED, BadgeType::DANGER],
    'queued' => [DeploymentStatus::QUEUED, BadgeType::INFO],
    'running' => [DeploymentStatus::RUNNING, BadgeType::INFO],
    'succeeded' => [DeploymentStatus::SUCCEEDED, BadgeType::SUCCESS],
]);
