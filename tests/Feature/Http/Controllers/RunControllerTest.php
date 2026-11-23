<?php

declare(strict_types=1);

use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Contracts\Console\Kernel;
use Illuminate\Foundation\Console\ClosureCommand;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Http\Response;
use Illuminate\Pagination\LengthAwarePaginator;
use Maarheeze\Compass\Collections\ScheduledCommandCollection;
use Maarheeze\Compass\Collector\CollectorType;
use Maarheeze\Compass\Collector\TaskType;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Http\RunTypeFilter;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunFailure;
use Maarheeze\Compass\Models\RunResult;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;
use Maarheeze\Compass\View\Filter;
use Maarheeze\Compass\View\FilterOption;
use Webmozart\Assert\Assert;

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

test('index loads the results of the runs', function (): void {
    RunResult::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns): bool {
        return $viewRuns->sole()->relationLoaded('results');
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

test('index on a collector type contains only the runs of that collector', function (): void {
    $sitesRun = Run::factory()->create(['collector' => CollectorType::SITES]);
    Run::factory()->create(['collector' => CollectorType::REPOSITORIES]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS, RunTypeFilter::collectorTypeRouteParameters(CollectorType::SITES)));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns) use ($sitesRun): bool {
        return $viewRuns->sole()->is($sitesRun);
    });
});

test('index on a task type contains only the runs of that task', function (): void {
    $siteEndpointsRun = Run::factory()->create([
        'source' => null,
        'collector' => null,
        'task' => TaskType::SITE_ENDPOINTS,
    ]);
    Run::factory()->create();
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS, RunTypeFilter::taskTypeRouteParameters(TaskType::SITE_ENDPOINTS)));

    $response->assertViewHas('runs', static function (LengthAwarePaginator $viewRuns) use ($siteEndpointsRun): bool {
        return $viewRuns->sole()->is($siteEndpointsRun);
    });
});

test('index on a collector type contains a run types filter counting all runs', function (): void {
    Run::factory()->create(['collector' => CollectorType::REPOSITORIES]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS, RunTypeFilter::collectorTypeRouteParameters(CollectorType::SITES)));

    $response->assertViewHas('runTypesFilter', static function (Filter $viewRunTypesFilter): bool {
        $repositoriesQueryParameters = RunTypeFilter::collectorTypeRouteParameters(CollectorType::REPOSITORIES);
        $repositoriesOption = $viewRunTypesFilter->options->firstOrFail(
            static function (FilterOption $filterOption) use ($repositoriesQueryParameters): bool {
                return $filterOption->queryParameters === $repositoriesQueryParameters;
            },
        );

        return $repositoriesOption->count === 1;
    });
});

test('index refuses an unknown collector type', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS, [RunTypeFilter::COLLECTOR_PARAMETER => fake()->word()]));

    $response->assertInvalid(RunTypeFilter::COLLECTOR_PARAMETER);
});

test('index refuses an unknown task type', function (): void {
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS, [RunTypeFilter::TASK_PARAMETER => fake()->word()]));

    $response->assertInvalid(RunTypeFilter::TASK_PARAMETER);
});

test('index lists the scheduled commands', function (): void {
    $command = sprintf('compass:%s', fake()->slug());
    $schedule = new Schedule();
    $schedule->command($command);
    $this->app->instance(Schedule::class, $schedule);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::RUNS));

    $response->assertViewHas(
        'scheduledCommands',
        static function (ScheduledCommandCollection $viewScheduledCommands) use ($command): bool {
            return $viewScheduledCommands->sole()->command === $command;
        },
    );
});

test('run runs the scheduled command of the mutex name', function (): void {
    $isRun = false;
    $kernel = Assert::isInstanceOf($this->app->make(Kernel::class), ConsoleKernel::class);
    $kernel->registerCommand(new ClosureCommand('compass:fake-command', function () use (&$isRun): void {
        $isRun = true;
    }));
    $schedule = new Schedule();
    $event = $schedule->command('compass:fake-command');
    $this->app->instance(Schedule::class, $schedule);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $this->post(route(RouteName::RUNS_RUN), ['mutex_name' => $event->mutexName()]);

    expect($isRun)
        ->toBeTrue();
});

test('run sends the user back to the runs page', function (): void {
    $kernel = Assert::isInstanceOf($this->app->make(Kernel::class), ConsoleKernel::class);
    $kernel->registerCommand(new ClosureCommand('compass:fake-command', function (): void {
    }));
    $schedule = new Schedule();
    $event = $schedule->command('compass:fake-command');
    $this->app->instance(Schedule::class, $schedule);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::RUNS_RUN), ['mutex_name' => $event->mutexName()]);

    $response->assertRedirect(route(RouteName::RUNS));
});

test('run answers a request for json with an empty accepted', function (): void {
    $kernel = Assert::isInstanceOf($this->app->make(Kernel::class), ConsoleKernel::class);
    $kernel->registerCommand(new ClosureCommand('compass:fake-command', function (): void {
    }));
    $schedule = new Schedule();
    $event = $schedule->command('compass:fake-command');
    $this->app->instance(Schedule::class, $schedule);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->postJson(route(RouteName::RUNS_RUN), ['mutex_name' => $event->mutexName()]);

    $response->assertNoContent(Response::HTTP_ACCEPTED);
});

test('run refuses a mutex name of no scheduled command', function (): void {
    $this->app->instance(Schedule::class, new Schedule());
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->post(route(RouteName::RUNS_RUN), ['mutex_name' => fake()->sha1()]);

    $response->assertSessionHasErrors('mutex_name');
});
