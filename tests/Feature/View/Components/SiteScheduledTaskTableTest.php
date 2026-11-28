<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\SiteScheduledTask;
use Maarheeze\Compass\View\Components\SiteScheduledTaskTable;

test('scheduled tasks are the tasks of the reports whose scheduler is judged', function (): void {
    $collectedAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'scheduler_reported' => true,
        'collected_at' => $collectedAt,
        'scheduler_last_ran_at' => $collectedAt,
        'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
    ]);
    $siteScheduledTask = SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $siteScheduledTaskTable = $this->app->make(SiteScheduledTaskTable::class, ['site' => $siteReport->site]);

    expect($siteScheduledTaskTable->scheduledTasks->modelKeys())
        ->toEqual([$siteScheduledTask->getKey()]);
});

test('scheduled tasks leave out the tasks of a report that is not installed', function (): void {
    $siteReport = SiteReport::factory()->create([
        'status' => fake()->randomElement([
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::NOT_INSTALLED,
            SiteReportStatus::UNAUTHORISED,
        ]),
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $siteScheduledTaskTable = $this->app->make(SiteScheduledTaskTable::class, ['site' => $siteReport->site]);

    expect($siteScheduledTaskTable->scheduledTasks)
        ->toBeEmpty();
});

test('timing describes the cron expression of the task', function (): void {
    $siteScheduledTask = SiteScheduledTask::factory()->make(['expression' => '0 7 * * *']);

    $siteScheduledTaskTable = $this->app->make(SiteScheduledTaskTable::class, ['site' => Site::factory()->create()]);
    $timing = $siteScheduledTaskTable->timing($siteScheduledTask);

    expect($timing)
        ->toBe('Every day at 07:00');
});

test('render renders the site scheduled task table view', function (): void {
    $siteScheduledTaskTable = $this->app->make(SiteScheduledTaskTable::class, ['site' => Site::factory()->create()]);

    expect($siteScheduledTaskTable->render()->name())
        ->toBe('compass::components.site-scheduled-task-table');
});
