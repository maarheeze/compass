<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;
use Maarheeze\Compass\View\Components\AlertSubjectName;

test('render renders the name of a repository', function (): void {
    $repository = Repository::factory()->create();

    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => $repository]);

    expect($alertSubjectName->render()->toHtml())
        ->toBe($repository->name);
});

test('render renders the name of a site', function (): void {
    $site = Site::factory()->create();

    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => $site]);

    expect($alertSubjectName->render()->toHtml())
        ->toBe($site->name);
});

test('render renders the site name followed by the path of a site endpoint', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();

    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => $siteEndpoint]);

    expect($alertSubjectName->render()->toHtml())
        ->toBe(sprintf('%s%s', $siteEndpoint->siteReport->site->name, $siteEndpoint->path->toString()));
});

test('render renders the site name followed by the source of a site report', function (): void {
    $siteReport = SiteReport::factory()->create();

    $alertSubjectName = $this->app->make(AlertSubjectName::class, ['subject' => $siteReport]);

    expect($alertSubjectName->render()->toHtml())
        ->toBe(sprintf('%s (%s)', $siteReport->site->name, $siteReport->source));
});
