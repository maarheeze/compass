<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('index lists the latest run first', function (): void {
    $earlierRun = Run::factory()->create(['created_at' => fake()->dateTimeBetween('-2 months', '-1 month')]);
    $laterRun = Run::factory()->create(['created_at' => fake()->dateTimeBetween('-1 month')]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS));

    $response->assertViewHas(
        'runs',
        static function (LengthAwarePaginator $viewRuns) use ($earlierRun, $laterRun): bool {
            return $viewRuns->first()->id->equals($laterRun->id) && $viewRuns->last()->id->equals($earlierRun->id);
        },
    );
});

test('type contains the runs of the type', function (): void {
    $run = Run::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS_OF_TYPE, $run->type));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns) use ($run): bool {
        return $viewRuns->sole()->id->equals($run->id);
    });
});

test('type leaves out the runs of another type', function (): void {
    Run::factory()->create(['type' => RunType::GITHUB_SYNC]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS_OF_TYPE, RunType::FORGE_SYNC));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns): bool {
        return $viewRuns->isEmpty();
    });
});
