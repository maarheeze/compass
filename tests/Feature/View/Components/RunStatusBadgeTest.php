<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\RunStatusBadge;

test('badge type follows the run status', function (RunStatus $runStatus, BadgeType $badgeType): void {
    $runStatusBadge = $this->app->make(RunStatusBadge::class, ['status' => $runStatus]);

    expect($runStatusBadge->badgeType())
        ->toBe($badgeType);
})->with([
    'failed' => [RunStatus::FAILED, BadgeType::DANGER],
    'queued' => [RunStatus::QUEUED, BadgeType::NEUTRAL],
    'running' => [RunStatus::RUNNING, BadgeType::INFO],
    'succeeded' => [RunStatus::SUCCEEDED, BadgeType::SUCCESS],
]);
