<?php

declare(strict_types=1);

use Illuminate\Log\Context\Repository as Context;
use Maarheeze\Compass\Collector\RecordRunResult;
use Maarheeze\Compass\Collector\RequestBudgetExhausted;
use Maarheeze\Compass\Collector\RunRecorder;
use Maarheeze\Compass\Collector\RunResultStatus;
use Maarheeze\Compass\Collector\SiteEndpoint\Jobs\CheckSiteEndpoints;
use Maarheeze\Compass\Collector\SiteReports\Jobs\InspectSite;
use Maarheeze\Compass\Models\Run;
use Maarheeze\Compass\Models\RunResult;
use Maarheeze\Compass\Models\Site;

test('handle records a succeeded result of the subject in the run of the job', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());
    $site = Site::factory()->create();

    $recordRunResult = $this->app->make(RecordRunResult::class);
    $recordRunResult->handle(new InspectSite($site, fake()->slug()), static function (): void {
    });

    $this->assertDatabaseHas(RunResult::class, [
        'run_id' => $run->id,
        'subject_type' => $site->getMorphClass(),
        'subject_id' => $site->id,
        'status' => RunResultStatus::SUCCEEDED,
        'messages' => json_encode([]),
    ]);
});

test('handle records a failed result of the subject with the messages of the exception', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());
    $site = Site::factory()->create();
    $exception = new RuntimeException(fake()->sentence());

    $recordRunResult = $this->app->make(RecordRunResult::class);

    expect(static function () use ($exception, $recordRunResult, $site): void {
        $recordRunResult->handle(new InspectSite($site, fake()->slug()), static function () use ($exception): void {
            throw $exception;
        });
    })
        ->toThrow(RuntimeException::class);
    $this->assertDatabaseHas(RunResult::class, [
        'run_id' => $run->id,
        'subject_type' => $site->getMorphClass(),
        'subject_id' => $site->id,
        'status' => RunResultStatus::FAILED,
        'messages' => json_encode([$exception->getMessage()]),
    ]);
});

test('handle passes the exception of a failed job on', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());

    $recordRunResult = $this->app->make(RecordRunResult::class);
    $recordRunResult->handle(new InspectSite(Site::factory()->create(), fake()->slug()), static function (): void {
        throw new RuntimeException(fake()->sentence());
    });
})->throws(RuntimeException::class);

test('handle records nothing for a job that skips its subject', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());
    $missingSite = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $recordRunResult = $this->app->make(RecordRunResult::class);
    $recordRunResult->handle(new InspectSite($missingSite, fake()->slug()), static function (): void {
    });

    $this->assertDatabaseEmpty(RunResult::class);
});

test('handle records nothing for a job without a subject', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());

    $recordRunResult = $this->app->make(RecordRunResult::class);
    $recordRunResult->handle(new CheckSiteEndpoints(), static function (): void {
    });

    $this->assertDatabaseEmpty(RunResult::class);
});

test('handle runs the job', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());
    $inspectSite = new InspectSite(Site::factory()->create(), fake()->slug());
    $ranInspectSite = null;

    $recordRunResult = $this->app->make(RecordRunResult::class);
    $recordRunResult->handle($inspectSite, static function (InspectSite $handledInspectSite) use (&$ranInspectSite): void {
        $ranInspectSite = $handledInspectSite;
    });

    expect($ranInspectSite)
        ->toBe($inspectSite);
});

test('handle records nothing for a job waiting for its request budget', function (): void {
    $run = Run::factory()->create();
    $this->app->make(Context::class)->add(RunRecorder::RUN_ID_CONTEXT_KEY, $run->id->toString());

    $recordRunResult = $this->app->make(RecordRunResult::class);

    expect(static function () use ($recordRunResult): void {
        $recordRunResult->handle(new InspectSite(Site::factory()->create(), fake()->slug()), static function (): void {
            throw new RequestBudgetExhausted(fake()->numberBetween(1, 60));
        });
    })
        ->toThrow(RequestBudgetExhausted::class);
    $this->assertDatabaseEmpty(RunResult::class);
});
