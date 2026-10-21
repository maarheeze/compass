<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\ComposerWarning;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\Composer\WarningBadge;

test('composer warning is the warning detected for the repository', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);

    $warningBadge = $this->app->make(WarningBadge::class, ['repository' => $repository]);

    expect($warningBadge->composerWarning)
        ->toBe(ComposerWarning::MISSING_TYPE);
});
