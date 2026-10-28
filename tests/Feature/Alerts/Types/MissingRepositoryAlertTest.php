<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\MissingRepositoryAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Repository;

test('description names the repository', function (): void {
    $repository = Repository::factory()->create();
    $alert = Alert::factory()
        ->missingRepository()
        ->for($repository, 'subject')
        ->create();

    $missingRepositoryAlert = $this->app->make(MissingRepositoryAlert::class);
    $description = $missingRepositoryAlert->description($alert);

    expect($description)
        ->toBe($repository->name);
});
