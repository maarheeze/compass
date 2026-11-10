<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\AlertSubjectName;

test('subject view is the view of a repository', function (): void {
    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => Repository::factory()->create()]);

    expect($alertSubjectName->render()->getData()['subjectView'])
        ->toBe('compass::alert-subjects.repository');
});

test('subject view is the view of a site', function (): void {
    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => Site::factory()->create()]);

    expect($alertSubjectName->render()->getData()['subjectView'])
        ->toBe('compass::alert-subjects.site');
});

test('subject view is the view of a site endpoint', function (): void {
    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => SiteEndpoint::factory()->create()]);

    expect($alertSubjectName->render()->getData()['subjectView'])
        ->toBe('compass::alert-subjects.site-endpoint');
});

test('subject view is the view of a site report', function (): void {
    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => SiteReport::factory()->create()]);

    expect($alertSubjectName->render()->getData()['subjectView'])
        ->toBe('compass::alert-subjects.site-report');
});

test('render hands the subject to its view', function (): void {
    $site = Site::factory()->create();

    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => $site]);

    expect($alertSubjectName->render()->getData()['subject'])
        ->toBe($site);
});

test('render renders the alert subject name view', function (): void {
    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => Site::factory()->create()]);

    expect($alertSubjectName->render()->name())
        ->toBe('compass::components.alert-subject-name');
});
