<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Alerts;
use Maarheeze\Compass\Collections\RepositoryCollection;
use Maarheeze\Compass\Collections\SiteCollection;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('is empty without repositories with a composer warning and without sites behind', function (): void {
    $alerts = new Alerts(new RepositoryCollection(), new SiteCollection());
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeTrue();
});

test('is not empty with a repository with a composer warning', function (): void {
    $alerts = new Alerts(new RepositoryCollection([Repository::factory()->make()]), new SiteCollection());
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeFalse();
});

test('is not empty with a site behind', function (): void {
    $alerts = new Alerts(new RepositoryCollection(), new SiteCollection([Site::factory()->make()]));
    $isEmpty = $alerts->isEmpty();

    expect($isEmpty)
        ->toBeFalse();
});
