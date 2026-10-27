<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\ClientStatusBadge;

test('badge type follows the client status', function (?ClientStatus $clientStatus, BadgeType $badgeType): void {
    $clientStatusBadge = $this->app->make(ClientStatusBadge::class, ['status' => $clientStatus]);

    expect($clientStatusBadge->badgeType())
        ->toBe($badgeType);
})->with([
    'incompatible' => [ClientStatus::INCOMPATIBLE, BadgeType::DANGER],
    'installed' => [ClientStatus::INSTALLED, BadgeType::SUCCESS],
    'not installed' => [ClientStatus::NOT_INSTALLED, BadgeType::NEUTRAL],
    'unauthorized' => [ClientStatus::UNAUTHORIZED, BadgeType::DANGER],
    'unreachable' => [ClientStatus::UNREACHABLE, BadgeType::NEUTRAL],
    'not checked' => [null, BadgeType::NEUTRAL],
]);
