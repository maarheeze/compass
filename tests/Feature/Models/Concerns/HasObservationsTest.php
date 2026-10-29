<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Models\Observation;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

test('deleting a subject deletes its observations', function (Model $subject): void {
    $observation = Observation::factory()
        ->for($subject, 'subject')
        ->create();

    $subject->delete();

    $this->assertModelMissing($observation);
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
]);

test('deleting a subject keeps the observations of another subject', function (Model $subject): void {
    $otherObservation = Observation::factory()->create();

    $subject->delete();

    $this->assertModelExists($otherObservation);
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
]);
