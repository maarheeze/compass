<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Bus\QueueingDispatcher;
use Illuminate\Pagination\LengthAwarePaginator;
use Illuminate\Support\Testing\Fakes\BusFake;
use Maarheeze\Compass\Collections\ScheduledCommandCollection;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Console\CheckSiteEndpointsCommand;
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

test('index loads the failures of the runs', function (): void {
    RunFailure::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS));

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

test('index lists the scheduled commands', function (): void {
    $schedule = new Schedule();
    $schedule->command(CheckSiteEndpointsCommand::SIGNATURE);
    $this->app->instance(Schedule::class, $schedule);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS));

    $response->assertViewHas(
        'scheduledCommands',
        static function (ScheduledCommandCollection $viewScheduledCommands): bool {
            return $viewScheduledCommands->sole()->command === CheckSiteEndpointsCommand::SIGNATURE;
        },
    );
});

test('run runs the scheduled command of the mutex name', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $schedule = new Schedule();
    $event = $schedule->command(CheckSiteEndpointsCommand::SIGNATURE);
    $this->app->instance(Schedule::class, $schedule);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::RUNS_RUN), ['mutex_name' => $event->mutexName()]);

    $this->assertDatabaseHas(Run::class, [
        'task' => TaskType::SITE_ENDPOINTS,
    ]);
});

test('run sends the user back to the runs page', function (): void {
    $this->app->instance(QueueingDispatcher::class, new BusFake($this->app->make(QueueingDispatcher::class)));
    $schedule = new Schedule();
    $event = $schedule->command(CheckSiteEndpointsCommand::SIGNATURE);
    $this->app->instance(Schedule::class, $schedule);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::RUNS_RUN), ['mutex_name' => $event->mutexName()]);

    $response->assertRedirect(route(RouteName::RUNS));
});

test('run refuses a mutex name of no scheduled command', function (): void {
    $this->app->instance(Schedule::class, new Schedule());
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::RUNS_RUN), ['mutex_name' => fake()->sha1()]);

    $response->assertSessionHasErrors('mutex_name');
});
