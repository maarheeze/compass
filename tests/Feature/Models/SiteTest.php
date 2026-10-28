<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Site;

test('display name is the name of the site', function (): void {
    $site = Site::factory()->create();

    $displayName = $site->displayName();

    expect($displayName)
        ->toBe($site->name);
});
