<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\ItemsPerPage;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunFailure;

test('listed is the runs newest first', function (): void {
    $earlierRun = Run::factory()->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterRun = Run::factory()->create(['created_at' => fake()->dateTimeBetween('-1 month')]);

    $listedRuns = Run::query()->listed(new ItemsPerPage(fake()->numberBetween(2, 100)));

    expect($listedRuns->first()->id->equals($laterRun->id) && $listedRuns->last()->id->equals($earlierRun->id))
        ->toBeTrue();
});

test('listed loads the failures of the runs', function (): void {
    RunFailure::factory()->create();

    $listedRuns = Run::query()->listed(new ItemsPerPage(fake()->numberBetween(1, 100)));

    expect($listedRuns->sole()->relationLoaded('failures'))
        ->toBeTrue();
});

test('listed has the given number of runs per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);

    $listedRuns = Run::query()->listed(new ItemsPerPage($itemsPerPage));

    expect($listedRuns->perPage())
        ->toBe($itemsPerPage);
});
