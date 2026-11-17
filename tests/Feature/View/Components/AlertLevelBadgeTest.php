<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\SiteEndpointDown;
use Maarheeze\Compass\View\AlertLevelBadgeType;
use Maarheeze\Compass\View\Components\AlertLevelBadge;

test('alert level is the level of the alert type with the identifier', function (): void {
    $siteEndpointDown = new SiteEndpointDown();

    $alertLevelBadge = $this->app->make(AlertLevelBadge::class, ['identifier' => $siteEndpointDown->identifier()]);

    expect($alertLevelBadge->alertLevel)
        ->toBe($siteEndpointDown->level());
});

test('badge type is the badge type of the level', function (): void {
    $siteEndpointDown = new SiteEndpointDown();

    $alertLevelBadge = $this->app->make(AlertLevelBadge::class, ['identifier' => $siteEndpointDown->identifier()]);

    expect($alertLevelBadge->badgeType)
        ->toBe(new AlertLevelBadgeType()->badgeType($siteEndpointDown->level()));
});

test('render renders the alert level badge view', function (): void {
    $alertLevelBadge = $this->app->make(AlertLevelBadge::class, ['identifier' => new SiteEndpointDown()->identifier()]);

    expect($alertLevelBadge->render()->name())
        ->toBe('compass::components.alert-level-badge');
});
