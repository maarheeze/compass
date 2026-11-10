<?php

declare(strict_types=1);

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Support\Testing\Fakes\EventFake;
use Maarheeze\Compass\Collector\Events\RunSaved;
use Maarheeze\Compass\Collector\Events\RunStatusChanged;
use Maarheeze\Compass\Collector\Listeners\BroadcastRunStatus;
use Maarheeze\Compass\Collector\RunStatus;
use Maarheeze\Compass\Models\Run;

test('handle broadcasts the toast of the run', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::RUNNING]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);

    $broadcastRunStatus = $this->app->make(BroadcastRunStatus::class);
    $broadcastRunStatus->handle(new RunSaved($run));

    $eventFake->assertDispatched(RunStatusChanged::class, static function (RunStatusChanged $runStatusChanged) use ($run): bool {
        return $runStatusChanged->toastHtml !== null
            && str_contains($runStatusChanged->toastHtml, sprintf('data-run-id="%s"', $run->id));
    });
});

test('handle broadcasts no toast for a queued run', function (): void {
    $run = Run::factory()->create(['status' => RunStatus::QUEUED]);
    $eventFake = new EventFake($this->app->make(Dispatcher::class));
    $this->app->instance(Dispatcher::class, $eventFake);

    $broadcastRunStatus = $this->app->make(BroadcastRunStatus::class);
    $broadcastRunStatus->handle(new RunSaved($run));

    $eventFake->assertDispatched(RunStatusChanged::class, static function (RunStatusChanged $runStatusChanged): bool {
        return $runStatusChanged->toastHtml === null;
    });
});
