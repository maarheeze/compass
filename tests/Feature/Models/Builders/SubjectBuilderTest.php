<?php

declare(strict_types=1);

use Maarheeze\Compass\Models\Alert;
use Maarheeze\Compass\Models\AlertCheck;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\SiteEndpoint;
use Maarheeze\Compass\Models\SiteReport;

test('of site or its reports keeps a check of the site', function (): void {
    $site = Site::factory()->create();
    $siteAlertCheck = AlertCheck::factory()
        ->for($site, 'subject')
        ->create();

    $alertChecksOfSite = AlertCheck::query()
        ->ofSiteOrItsReports($site)
        ->get();

    expect($alertChecksOfSite->sole()->is($siteAlertCheck))
        ->toBeTrue();
});

test('of site or its reports keeps a check of a report of the site', function (): void {
    $siteReport = SiteReport::factory()->create();
    $siteReportAlertCheck = AlertCheck::factory()
        ->for($siteReport, 'subject')
        ->create();

    $alertChecksOfSite = AlertCheck::query()
        ->ofSiteOrItsReports($siteReport->site)
        ->get();

    expect($alertChecksOfSite->sole()->is($siteReportAlertCheck))
        ->toBeTrue();
});

test('of site or its reports leaves out a check of an endpoint of the site', function (): void {
    $siteEndpoint = SiteEndpoint::factory()->create();
    AlertCheck::factory()
        ->for($siteEndpoint, 'subject')
        ->create();

    $alertChecksOfSite = AlertCheck::query()
        ->ofSiteOrItsReports($siteEndpoint->siteReport->site)
        ->get();

    expect($alertChecksOfSite)
        ->toBeEmpty();
});

test('of site or its reports leaves out the checks of another site and its reports', function (): void {
    AlertCheck::factory()
        ->for(Site::factory(), 'subject')
        ->create();
    AlertCheck::factory()
        ->for(SiteReport::factory(), 'subject')
        ->create();

    $alertChecksOfSite = AlertCheck::query()
        ->ofSiteOrItsReports(Site::factory()->create())
        ->get();

    expect($alertChecksOfSite)
        ->toBeEmpty();
});

test('of subject keeps a check of the subject', function (): void {
    $alertCheck = AlertCheck::factory()->create();

    $alertChecksOfSubject = AlertCheck::query()
        ->ofSubject($alertCheck->subject)
        ->get();

    expect($alertChecksOfSubject->sole()->is($alertCheck))
        ->toBeTrue();
});

test('of subject leaves out a check of another subject', function (): void {
    AlertCheck::factory()->create();
    $repository = Repository::factory()->create();

    $alertChecksOfSubject = AlertCheck::query()
        ->ofSubject($repository)
        ->get();

    expect($alertChecksOfSubject)
        ->toBeEmpty();
});

test('of subject keeps an alert of the subject', function (): void {
    $repository = Repository::factory()->create();
    $repositoryAlert = Alert::factory()
        ->repositoryMissing()
        ->for($repository, 'subject')
        ->create();

    $alertsOfSubject = Alert::query()
        ->ofSubject($repository)
        ->get();

    expect($alertsOfSubject->sole()->is($repositoryAlert))
        ->toBeTrue();
});

test('of subject leaves out the alerts of another subject', function (): void {
    Alert::factory()
        ->repositoryMissing()
        ->create();

    $alertsOfSubject = Alert::query()
        ->ofSubject(Repository::factory()->create())
        ->get();

    expect($alertsOfSubject)
        ->toBeEmpty();
});
