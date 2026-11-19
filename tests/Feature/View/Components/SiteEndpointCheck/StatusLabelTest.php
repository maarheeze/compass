<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\SiteEndpoint\SiteEndpointState;
use Maarheeze\Compass\Models\SiteEndpointCheck;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\SiteEndpointCheck\StatusLabel;

test('badge type follows the site endpoint state', function (SiteEndpointState $siteEndpointState, BadgeType $badgeType): void {
    $statusLabel = $this->app->make(StatusLabel::class, [
        'siteEndpointCheck' => SiteEndpointCheck::factory()->make(['state' => $siteEndpointState]),
    ]);

    expect($statusLabel->badgeType())
        ->toBe($badgeType);
})->with([
    'down' => [SiteEndpointState::DOWN, BadgeType::DANGER],
    'up' => [SiteEndpointState::UP, BadgeType::SUCCESS],
]);

test('render renders the status label view', function (): void {
    $statusLabel = $this->app->make(StatusLabel::class, [
        'siteEndpointCheck' => SiteEndpointCheck::factory()->make(),
    ]);

    expect($statusLabel->render()->name())
        ->toBe('compass::components.site-endpoint-check.status-label');
});
