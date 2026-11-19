<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Sites\DeploymentStatus;
use Maarheeze\Compass\View\Components\DeploymentStatusBadge;
use Maarheeze\Compass\View\DeploymentStatusBadgeType;

test('badge type is the badge type of the status', function (): void {
    $deploymentStatus = fake()->randomElement(DeploymentStatus::cases());

    $deploymentStatusBadge = $this->app->make(DeploymentStatusBadge::class, ['status' => $deploymentStatus]);

    expect($deploymentStatusBadge->badgeType)
        ->toBe(new DeploymentStatusBadgeType()->badgeType($deploymentStatus));
});

test('render renders the deployment status badge view', function (): void {
    $deploymentStatusBadge = $this->app->make(DeploymentStatusBadge::class, [
        'status' => fake()->randomElement(DeploymentStatus::cases()),
    ]);

    expect($deploymentStatusBadge->render()->name())
        ->toBe('compass::components.deployment-status-badge');
});
