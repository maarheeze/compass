<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\AlertIcon;

test('should render for a subject with an alert that is not done', function (): void {
    $repository = Repository::factory()->create();
    Alert::factory()
        ->composerProjectWithoutLock()
        ->active()
        ->for($repository, 'subject')
        ->create();

    $alertIcon = $this->app->make(AlertIcon::class, ['subject' => $repository]);

    expect($alertIcon->shouldRender())
        ->toBeTrue();
});

test('should render for a site with an alert of its report that is not done', function (): void {
    $siteReport = SiteReport::factory()->create();
    Alert::factory()
        ->siteDebugEnabled()
        ->active()
        ->for($siteReport, 'subject')
        ->create();

    $alertIcon = $this->app->make(AlertIcon::class, ['subject' => $siteReport->site]);

    expect($alertIcon->shouldRender())
        ->toBeTrue();
});

test('should not render for a subject with only done alerts', function (): void {
    $repository = Repository::factory()->create();
    Alert::factory()
        ->composerProjectWithoutLock()
        ->done()
        ->for($repository, 'subject')
        ->create();

    $alertIcon = $this->app->make(AlertIcon::class, ['subject' => $repository]);

    expect($alertIcon->shouldRender())
        ->toBeFalse();
});

test('has alerts not muted is true for a subject with an alert that is not muted', function (): void {
    $repository = Repository::factory()->create();
    Alert::factory()
        ->composerProjectWithoutLock()
        ->active()
        ->for($repository, 'subject')
        ->create();

    $alertIcon = $this->app->make(AlertIcon::class, ['subject' => $repository]);

    expect($alertIcon->hasAlertsNotMuted)
        ->toBeTrue();
});

test('has alerts not muted is false for a subject with only muted alerts', function (): void {
    $repository = Repository::factory()->create();
    Alert::factory()
        ->composerProjectWithoutLock()
        ->active()
        ->muted()
        ->for($repository, 'subject')
        ->create();

    $alertIcon = $this->app->make(AlertIcon::class, ['subject' => $repository]);

    expect($alertIcon->hasAlertsNotMuted)
        ->toBeFalse();
});

test('render renders the alert icon view', function (): void {
    $alertIcon = $this->app->make(AlertIcon::class, ['subject' => Repository::factory()->create()]);

    expect($alertIcon->render()->name())
        ->toBe('compass::components.alert-icon');
});
