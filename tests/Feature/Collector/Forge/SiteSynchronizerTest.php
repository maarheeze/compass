<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\ForgeSiteCollection;
use Maarheeze\Compass\Collector\Forge\ForgeSite;
use Maarheeze\Compass\Collector\Forge\SiteStatus;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;
use Maarheeze\Compass\Collector\RunId;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Tests\Fixtures\ForgeSiteFactory;

test('store sites creates a site for a new forge site', function (): void {
    $forgeSite = ForgeSiteFactory::make();
    $runId = RunId::generate();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), $runId);

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $forgeSite->forgeId,
        'name' => $forgeSite->name,
        'url' => $forgeSite->url,
        'status' => $forgeSite->status,
        'php_version' => $forgeSite->phpVersion,
        'repository_url' => $forgeSite->repositoryUrl,
        'repository_branch' => $forgeSite->repositoryBranch,
        'auto_deploy' => $forgeSite->autoDeploy,
        'server_name' => $forgeSite->serverName,
        'server_slug' => $forgeSite->serverSlug,
        'server_ip_address' => $forgeSite->serverIpAddress,
        'run_id' => $runId,
    ]);
});

test('store sites stores the latest deployment of a forge site', function (): void {
    $forgeSite = ForgeSiteFactory::make();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), RunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $forgeSite->forgeId,
        'deployment_status' => $forgeSite->latestDeployment?->status,
        'deployed_commit_hash' => $forgeSite->latestDeployment?->commitHash,
        'deployed_commit_message' => $forgeSite->latestDeployment?->commitMessage,
        'deployment_started_at' => $forgeSite->latestDeployment?->startedAt,
        'deployment_ended_at' => $forgeSite->latestDeployment?->endedAt,
    ]);
});

test('store sites stores no deployment for a forge site that was never deployed', function (): void {
    $forgeSite = ForgeSiteFactory::makeNeverDeployed();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), RunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $forgeSite->forgeId,
        'deployment_status' => null,
        'deployed_commit_hash' => null,
    ]);
});

test('store sites updates the name of a known site', function (): void {
    $site = Site::factory()->create(['name' => fake()->unique()->domainName()]);
    $renamedForgeSite = new ForgeSite(
        $site->forge_id,
        fake()->unique()->domainName(),
        fake()->url(),
        fake()->randomElement(SiteStatus::cases()),
        fake()->numerify('php8#'),
        fake()->url(),
        fake()->word(),
        fake()->optional()->boolean(),
        fake()->slug(),
        fake()->slug(),
        fake()->ipv4(),
        null,
    );

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$renamedForgeSite]), RunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'name' => $renamedForgeSite->name,
    ]);
});

test('store sites clears the missing marks of a known site', function (): void {
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);
    $forgeSite = ForgeSiteFactory::forSite($site);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), RunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);
});

test('mark uncollected missing marks a site of another run missing', function (): void {
    $site = Site::factory()->create();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->markUncollectedMissing(RunId::generate());

    expect($site->refresh()->missing_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('mark uncollected missing keeps the moment a site went missing', function (): void {
    $missingAt = fake()->dateTime();
    $site = Site::factory()->create(['missing_at' => $missingAt]);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->markUncollectedMissing(RunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'missing_at' => $missingAt,
    ]);
});

test('mark uncollected missing leaves a site of the run unmarked', function (): void {
    $runId = RunId::generate();
    $site = Site::factory()->create(['run_id' => $runId]);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->markUncollectedMissing($runId);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'missing_at' => null,
    ]);
});
