<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Repository;

test('display name is the name of the repository', function (): void {
    $repository = Repository::factory()->create();

    $displayName = $repository->displayName();

    expect($displayName)
        ->toBe($repository->name);
});
