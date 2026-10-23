<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Alerts;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('is empty without any alert', function (): void {
    $alerts = new Alerts(new RepositoryCollection(), new SiteCollection(), new RepositoryCollection(), new SiteCollection());
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeTrue();
});

test('is not empty with a missing repository', function (): void {
    $alerts = new Alerts(new RepositoryCollection([Repository::factory()->make()]), new SiteCollection(), new RepositoryCollection(), new SiteCollection());
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeFalse();
});

test('is not empty with a missing site', function (): void {
    $alerts = new Alerts(new RepositoryCollection(), new SiteCollection([Site::factory()->make()]), new RepositoryCollection(), new SiteCollection());
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeFalse();
});

test('is not empty with a repository with a composer warning', function (): void {
    $alerts = new Alerts(new RepositoryCollection(), new SiteCollection(), new RepositoryCollection([Repository::factory()->make()]), new SiteCollection());
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeFalse();
});

test('is not empty with a site behind', function (): void {
    $alerts = new Alerts(new RepositoryCollection(), new SiteCollection(), new RepositoryCollection(), new SiteCollection([Site::factory()->make()]));
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeFalse();
});
