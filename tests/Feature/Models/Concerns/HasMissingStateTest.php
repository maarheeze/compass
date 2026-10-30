<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\MissingState;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('missing state is collected for a subject that is not missing', function (Repository|Site $subject): void {
    $missingState = $subject->missingState();

    expect($missingState)
        ->toBe(MissingState::COLLECTED);
})->with([
    function (): Repository {
        return Repository::factory()->create(['missing_at' => null]);
    },
    function (): Site {
        return Site::factory()->create(['missing_at' => null]);
    },
]);

test('missing state is missing for a missing subject that is not ignored', function (Repository|Site $subject): void {
    $missingState = $subject->missingState();

    expect($missingState)
        ->toBe(MissingState::MISSING);
})->with([
    function (): Repository {
        return Repository::factory()->create([
            'missing_at' => fake()->dateTime(),
            'missing_ignored_at' => null,
        ]);
    },
    function (): Site {
        return Site::factory()->create([
            'missing_at' => fake()->dateTime(),
            'missing_ignored_at' => null,
        ]);
    },
]);

test('missing state is ignored for a missing subject that is ignored', function (Repository|Site $subject): void {
    $missingState = $subject->missingState();

    expect($missingState)
        ->toBe(MissingState::IGNORED);
})->with([
    function (): Repository {
        return Repository::factory()->create([
            'missing_at' => fake()->dateTime(),
            'missing_ignored_at' => fake()->dateTime(),
        ]);
    },
    function (): Site {
        return Site::factory()->create([
            'missing_at' => fake()->dateTime(),
            'missing_ignored_at' => fake()->dateTime(),
        ]);
    },
]);
