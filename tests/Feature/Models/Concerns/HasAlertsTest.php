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
