<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\AlertFinder;
use Maarheeze\Compass\Analyzer\Client\DebugEnabledAlert;
use Maarheeze\Compass\Analyzer\Client\NonProductionAlert;
use Maarheeze\Compass\Analyzer\Client\UnauthorizedClientAlert;
use Maarheeze\Compass\Analyzer\Composer\ComposerWarningAlert;
use Maarheeze\Compass\Analyzer\Forge\MissingSiteAlert;
use Maarheeze\Compass\Analyzer\Forge\SiteBehindAlert;
use Maarheeze\Compass\Analyzer\GitHub\MissingRepositoryAlert;
use Maarheeze\Compass\Collector\Client\ClientStatus;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;

test('find holds the missing repositories', function (): void {
    $repository = Repository::factory()->create(['missing_at' => fake()->dateTime()]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    $missingRepositoryAlert = $alerts->whereInstanceOf(MissingRepositoryAlert::class)->sole();
    expect($missingRepositoryAlert->repository->is($repository))
        ->toBeTrue();
});

test('find holds the missing sites', function (): void {
    $site = Site::factory()->create(['missing_at' => fake()->dateTime()]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    $missingSiteAlert = $alerts->whereInstanceOf(MissingSiteAlert::class)->sole();
    expect($missingSiteAlert->site->is($site))
        ->toBeTrue();
});

test('find holds the repositories with a composer warning', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => null,
    ]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    $composerWarningAlert = $alerts->whereInstanceOf(ComposerWarningAlert::class)->sole();
    expect($composerWarningAlert->repository->is($repository))
        ->toBeTrue();
});

test('find holds the sites behind', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
    ]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    $siteBehindAlert = $alerts->whereInstanceOf(SiteBehindAlert::class)->sole();
    expect($siteBehindAlert->site->is($site))
        ->toBeTrue();
});

test('find holds the sites outside production', function (): void {
    $site = Site::factory()->create(['app_env' => 'staging']);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    $nonProductionAlert = $alerts->whereInstanceOf(NonProductionAlert::class)->sole();
    expect($nonProductionAlert->site->is($site))
        ->toBeTrue();
});

test('find holds the sites with debug mode on', function (): void {
    $site = Site::factory()->create(['app_debug' => true]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    $debugEnabledAlert = $alerts->whereInstanceOf(DebugEnabledAlert::class)->sole();
    expect($debugEnabledAlert->site->is($site))
        ->toBeTrue();
});

test('find holds the sites whose client refuses the key', function (): void {
    $site = Site::factory()->create(['client_status' => ClientStatus::UNAUTHORIZED]);

    $alertFinder = $this->app->make(AlertFinder::class);
    $alerts = $alertFinder->find();

    $unauthorizedClientAlert = $alerts->whereInstanceOf(UnauthorizedClientAlert::class)->sole();
    expect($unauthorizedClientAlert->site->is($site))
        ->toBeTrue();
});
