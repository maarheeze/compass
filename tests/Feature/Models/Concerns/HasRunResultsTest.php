<?php

declare(strict_types=1);

use Illuminate\Database\Eloquent\Model;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\RunResult;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;

test('deleting a subject deletes its run results', function (Model $subject): void {
    $runResult = RunResult::factory()
        ->for($subject, 'subject')
        ->create();

    $subject->delete();

    $this->assertModelMissing($runResult);
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
