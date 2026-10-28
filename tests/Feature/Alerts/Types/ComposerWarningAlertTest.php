<?php

declare(strict_types=1);

use Maarheeze\Compass\Alerts\Types\ComposerWarningAlert;
use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\Repository;

test('description names the repository', function (): void {
    $repository = Repository::factory()->create();
    $alert = Alert::factory()
        ->composerWarning()
        ->for($repository, 'subject')
        ->create();

    $composerWarningAlert = $this->app->make(ComposerWarningAlert::class);
    $description = $composerWarningAlert->description($alert);

    expect($description)
        ->toBe($repository->name);
});
