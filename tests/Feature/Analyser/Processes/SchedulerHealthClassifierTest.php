<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealth;
use Maarheeze\Compass\Analyser\Processes\SchedulerHealthClassifier;
use Maarheeze\Compass\Collector\SiteReports\SiteReportStatus;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\Models\SiteScheduledTask;

test('classify is unknown when the client did not report', function (SiteReportStatus $siteReportStatus): void {
    $siteReport = SiteReport::factory()->create(['status' => $siteReportStatus]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $schedulerHealthClassifier = $this->app->make(SchedulerHealthClassifier::class);
    $schedulerHealth = $schedulerHealthClassifier->classify($siteReport);

    expect($schedulerHealth)
        ->toBe(SchedulerHealth::UNKNOWN);
})->with([
    'incompatible' => [SiteReportStatus::INCOMPATIBLE],
    'not installed' => [SiteReportStatus::NOT_INSTALLED],
    'unauthorised' => [SiteReportStatus::UNAUTHORISED],
]);

test('classify is no tasks when the site schedules none', function (): void {
    $siteReport = SiteReport::factory()->create(['status' => SiteReportStatus::INSTALLED]);

    $schedulerHealthClassifier = $this->app->make(SchedulerHealthClassifier::class);
    $schedulerHealth = $schedulerHealthClassifier->classify($siteReport);

    expect($schedulerHealth)
        ->toBe(SchedulerHealth::NO_TASKS);
});

test('classify is not running when the scheduler of a site with tasks never ran', function (): void {
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'scheduler_last_ran_at' => null,
        'scheduler_grace_minutes' => fake()->numberBetween(1, 60),
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $schedulerHealthClassifier = $this->app->make(SchedulerHealthClassifier::class);
    $schedulerHealth = $schedulerHealthClassifier->classify($siteReport);

    expect($schedulerHealth)
        ->toBe(SchedulerHealth::NOT_RUNNING);
});

test('classify is not running when the scheduler last ran longer than its grace before the report', function (): void {
    $collectedAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
    $schedulerGraceMinutes = fake()->numberBetween(1, 60);
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'collected_at' => $collectedAt,
        'scheduler_last_ran_at' => $collectedAt->subSeconds($schedulerGraceMinutes * 60 + fake()->numberBetween(1, 3600)),
        'scheduler_grace_minutes' => $schedulerGraceMinutes,
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $schedulerHealthClassifier = $this->app->make(SchedulerHealthClassifier::class);
    $schedulerHealth = $schedulerHealthClassifier->classify($siteReport);

    expect($schedulerHealth)
        ->toBe(SchedulerHealth::NOT_RUNNING);
});

test('classify is running when the scheduler last ran within its grace before the report', function (): void {
    $collectedAt = CarbonImmutable::instance(fake()->dateTime())->startOfSecond();
    $schedulerGraceMinutes = fake()->numberBetween(1, 60);
    $siteReport = SiteReport::factory()->create([
        'status' => SiteReportStatus::INSTALLED,
        'collected_at' => $collectedAt,
        'scheduler_last_ran_at' => $collectedAt->subSeconds(fake()->numberBetween(0, $schedulerGraceMinutes * 60)),
        'scheduler_grace_minutes' => $schedulerGraceMinutes,
    ]);
    SiteScheduledTask::factory()->create(['site_report_id' => $siteReport->id]);

    $schedulerHealthClassifier = $this->app->make(SchedulerHealthClassifier::class);
    $schedulerHealth = $schedulerHealthClassifier->classify($siteReport);

    expect($schedulerHealth)
        ->toBe(SchedulerHealth::RUNNING);
});
