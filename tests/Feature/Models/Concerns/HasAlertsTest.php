<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

test('deleting a subject deletes its alerts', function (Model $subject): void {
    $alert = Alert::factory()
        ->for($subject, 'subject')
        ->create();

    $subject->delete();

    $this->assertModelMissing($alert);
})->with([
    function (): Repository {
        return Repository::factory()->create();
    },
    function (): Site {
        return Site::factory()->create();
    },
    function (): SiteEndpoint {
        return SiteEndpoint::factory()->create();
    },
    function (): SiteReport {
        return SiteReport::factory()->create();
    },
]);

test('deleting a subject keeps the alerts of another subject', function (Model $subject): void {
    $otherAlert = Alert::factory()->create();

    $subject->delete();

    $this->assertModelExists($otherAlert);
})->with([
    function (): Repository {
        return Repository::factory()->create();
    },
    function (): Site {
        return Site::factory()->create();
    },
    function (): SiteEndpoint {
        return SiteEndpoint::factory()->create();
    },
    function (): SiteReport {
        return SiteReport::factory()->create();
    },
]);

test('alerts including children keeps an alert of the subject', function (): void {
    $repository = Repository::factory()->create();
    $repositoryAlert = Alert::factory()
        ->repositoryMissing()
        ->for($repository, 'subject')
        ->create();

    $alertsIncludingChildren = $repository
        ->alertsIncludingChildren()
        ->get();

    expect($alertsIncludingChildren->sole()->is($repositoryAlert))
        ->toBeTrue();
});

test('alerts including children leaves out the alerts of another subject', function (): void {
    Alert::factory()
        ->repositoryMissing()
        ->create();

    $alertsIncludingChildren = Repository::factory()
        ->create()
        ->alertsIncludingChildren()
        ->get();

    expect($alertsIncludingChildren)
        ->toBeEmpty();
});
