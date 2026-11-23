<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\RunResultStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\RunResultStatusBadge;

test('badge type follows the run result status', function (RunResultStatus $runResultStatus, BadgeType $badgeType): void {
    $runResultStatusBadge = $this->app->make(RunResultStatusBadge::class, ['status' => $runResultStatus]);

    expect($runResultStatusBadge->badgeType())
        ->toBe($badgeType);
})->with([
    'failed' => [RunResultStatus::FAILED, BadgeType::DANGER],
    'succeeded' => [RunResultStatus::SUCCEEDED, BadgeType::SUCCESS],
]);
