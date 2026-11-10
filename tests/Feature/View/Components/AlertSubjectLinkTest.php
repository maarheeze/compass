<?php

declare(strict_types=1);

use Maarheeze\Compass\Http\AlertSubjectUrl;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\View\Components\AlertSubjectLink;

test('url is the alert subject url of the subject', function (): void {
    $site = Site::factory()->create();

    $alertSubjectLink = $this->app->make(AlertSubjectLink::class, ['subject' => $site]);

    expect($alertSubjectLink->url)
        ->toBe($this->app->make(AlertSubjectUrl::class)->url($site));
});

test('render renders the alert subject link view', function (): void {
    $alertSubjectLink = $this->app->make(AlertSubjectLink::class, ['subject' => Site::factory()->create()]);

    expect($alertSubjectLink->render()->name())
        ->toBe('compass::components.alert-subject-link');
});
