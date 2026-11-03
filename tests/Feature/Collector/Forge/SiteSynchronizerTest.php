<?php

declare(strict_types=1);

use Carbon\CarbonImmutable;
use Maarheeze\Compass\Collections\CollectedSiteCollection;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;
use Maarheeze\Compass\Collector\Sites\CollectedSite;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Tests\Fixtures\CollectedSiteFactory;
use Maarheeze\Uuid\Uuid;

test('store sites creates a site for a new collected site', function (): void {
    $collectedSite = CollectedSiteFactory::make();
    $runId = Uuid::generate();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new CollectedSiteCollection([$collectedSite]), $runId);

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $collectedSite->externalId,
        'name' => $collectedSite->name,
        'url' => $collectedSite->url,
        'php_version' => $collectedSite->phpVersion,
        'repository_url' => $collectedSite->repositoryUrl,
        'repository_branch' => $collectedSite->repositoryBranch,
        'auto_deploy' => $collectedSite->autoDeploy,
        'server_name' => $collectedSite->serverName,
        'server_ip_address' => $collectedSite->serverIpAddress,
        'run_id' => $runId,
    ]);
});

test('store sites stores the latest deployment of a collected site', function (): void {
    $collectedSite = CollectedSiteFactory::make();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new CollectedSiteCollection([$collectedSite]), Uuid::generate());

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $collectedSite->externalId,
        'deployment_status' => $collectedSite->deploymentStatus,
        'deployed_commit_hash' => $collectedSite->deployedCommitHash,
        'deployment_started_at' => $collectedSite->deploymentStartedAt,
        'deployment_ended_at' => $collectedSite->deploymentEndedAt,
    ]);
});

test('store sites stores no deployment for a collected site that was never deployed', function (): void {
    $collectedSite = CollectedSiteFactory::neverDeployed();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new CollectedSiteCollection([$collectedSite]), Uuid::generate());

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $collectedSite->externalId,
        'deployment_status' => null,
        'deployed_commit_hash' => null,
    ]);
});

test('store sites updates the name of a known site', function (): void {
    $site = Site::factory()->create(['name' => fake()->unique()->domainName()]);
    $renamedCollectedSite = new CollectedSite(
        $site->forge_id,
        fake()->unique()->domainName(),
        fake()->url(),
        fake()->numerify('php8#'),
        fake()->url(),
        fake()->word(),
        fake()->optional()->boolean(),
        fake()->slug(),
        fake()->ipv4(),
        null,
        null,
        null,
        null,
    );

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new CollectedSiteCollection([$renamedCollectedSite]), Uuid::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'name' => $renamedCollectedSite->name,
    ]);
});

test('store sites clears the missing marks of a known site', function (): void {
    $site = Site::factory()->create([
        'missing_at' => fake()->dateTime(),
        'missing_ignored_at' => fake()->dateTime(),
    ]);
    $collectedSite = CollectedSiteFactory::forSite($site);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new CollectedSiteCollection([$collectedSite]), Uuid::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'missing_at' => null,
        'missing_ignored_at' => null,
    ]);
});

test('mark uncollected missing marks a site of another run missing', function (): void {
    $site = Site::factory()->create();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->markUncollectedMissing(Uuid::generate());

    expect($site->refresh()->missing_at)
        ->toBeInstanceOf(CarbonImmutable::class);
});

test('mark uncollected missing keeps the moment a site went missing', function (): void {
    $missingAt = fake()->dateTime();
    $site = Site::factory()->create(['missing_at' => $missingAt]);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->markUncollectedMissing(Uuid::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'missing_at' => $missingAt,
    ]);
});

test('mark uncollected missing leaves a site of the run unmarked', function (): void {
    $runId = Uuid::generate();
    $site = Site::factory()->create(['run_id' => $runId]);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->markUncollectedMissing($runId);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'missing_at' => null,
    ]);
});
