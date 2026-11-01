<?php

declare(strict_types=1);

use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Collector\RunType;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunFailure;
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

test('index loads the failures of the runs', function (): void {
    RunFailure::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns): bool {
        return $viewRuns->sole()->relationLoaded('failures');
    });
});

test('type loads the failures of the runs', function (): void {
    $run = Run::factory()->create();
    RunFailure::factory()->create(['run_id' => $run->id]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS_OF_TYPE, $run->type));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns): bool {
        return $viewRuns->sole()->relationLoaded('failures');
    });
});

test('index shows the configured number of runs per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);
    $this->setConfiguration('compass.items_per_page', $itemsPerPage);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns) use ($itemsPerPage): bool {
        return $viewRuns->perPage() === $itemsPerPage;
    });
});

test('type shows the configured number of runs per page', function (): void {
    $itemsPerPage = fake()->numberBetween(1, 100);
    $this->setConfiguration('compass.items_per_page', $itemsPerPage);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS_OF_TYPE, fake()->randomElement(RunType::cases())));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns) use ($itemsPerPage): bool {
        return $viewRuns->perPage() === $itemsPerPage;
    });
});
