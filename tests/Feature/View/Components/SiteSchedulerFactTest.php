<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealth;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\SiteScheduledTask;
use Maarheeze\Compass\View\BadgeType;
use Maarheeze\Compass\View\Components\SiteSchedulerFact;

test('scheduler health is the health of the scheduler of the report', function (): void {
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'scheduler_reported' => true,
    ]);

    $siteSchedulerFact = $this->app->make(SiteSchedulerFact::class, ['siteReport' => $siteReport]);

    expect($siteSchedulerFact->schedulerHealth)
        ->toBe(SchedulerHealth::NO_TASKS);
});

test('badge type is danger while the scheduler is not running', function (): void {
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'scheduler_reported' => true,
        'scheduler_last_ran_at' => null,
        'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $siteSchedulerFact = $this->app->make(SiteSchedulerFact::class, ['siteReport' => $siteReport]);

    expect($siteSchedulerFact->badgeType)
        ->toBe(BadgeType::DANGER);
});

test('scheduled task count is the number of tasks of a report whose scheduler is judged', function (): void {
    $collectedAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'scheduler_reported' => true,
        'collected_at' => $collectedAt,
        'scheduler_last_ran_at' => $collectedAt,
        'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
    ]);
    $scheduledTaskCount = fake()->numberBetween(1, 5);
    SiteScheduledTask::factory()
        ->count($scheduledTaskCount)
        ->create(['site_report_id' => $siteReport->id]);

    $siteSchedulerFact = $this->app->make(SiteSchedulerFact::class, ['siteReport' => $siteReport]);

    expect($siteSchedulerFact->scheduledTaskCount)
        ->toBe($scheduledTaskCount);
});

test('scheduled task count is null for a report that is not installed', function (): void {
    $siteReport = SiteReport::factory()->create([
        'status' => fake()->randomElement([
            SiteReportStatus::INCOMPATIBLE,
            SiteReportStatus::NOT_INSTALLED,
            SiteReportStatus::UNAUTHORISED,
        ]),
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $siteSchedulerFact = $this->app->make(SiteSchedulerFact::class, ['siteReport' => $siteReport]);

    expect($siteSchedulerFact->scheduledTaskCount)
        ->toBeNull();
});

test('render renders the site scheduler fact view', function (): void {
    $siteSchedulerFact = $this->app->make(SiteSchedulerFact::class, ['siteReport' => SiteReport::factory()->create()]);

    expect($siteSchedulerFact->render()->name())
        ->toBe('compass::components.site-scheduler-fact');
});
