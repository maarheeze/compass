<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\ForgeSiteCollection;
use Maarheeze\Compass\Collector\CollectionRunId;
use Maarheeze\Compass\Collector\Forge\ForgeSite;
use Maarheeze\Compass\Collector\Forge\SiteStatus;
use Maarheeze\Compass\Collector\Forge\SiteSynchronizer;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Tests\Fixtures\ForgeSiteFactory;

test('store sites creates a site for a new forge site', function (): void {
    $forgeSite = ForgeSiteFactory::make();
    $collectionRunId = CollectionRunId::generate();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), $collectionRunId);

    $this->assertDatabaseHas(Site::class, [
        'forge_id' => $forgeSite->forgeId,
        'name' => $forgeSite->name,
        'url' => $forgeSite->url,
        'status' => $forgeSite->status,
        'php_version' => $forgeSite->phpVersion,
        'repository_url' => $forgeSite->repositoryUrl,
        'repository_branch' => $forgeSite->repositoryBranch,
        'server_name' => $forgeSite->serverName,
        'server_slug' => $forgeSite->serverSlug,
        'server_ip_address' => $forgeSite->serverIpAddress,
        'collection_run_id' => $collectionRunId,
        'is_active' => true,
    ]);
});

test('store sites stores the latest deployment of a forge site', function (): void {
    $forgeSite = ForgeSiteFactory::make();

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), CollectionRunId::generate());

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
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), CollectionRunId::generate());

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
        fake()->slug(),
        fake()->slug(),
        fake()->ipv4(),
        null,
    );

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$renamedForgeSite]), CollectionRunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'name' => $renamedForgeSite->name,
    ]);
});

test('store sites reactivates an inactive site', function (): void {
    $site = Site::factory()->create(['is_active' => false]);
    $forgeSite = ForgeSiteFactory::forSite($site);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->storeSites(new ForgeSiteCollection([$forgeSite]), CollectionRunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'is_active' => true,
    ]);
});

test('update inactive marks a site of another run inactive', function (): void {
    $site = Site::factory()->create(['is_active' => true]);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->updateInactive(CollectionRunId::generate());

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'is_active' => false,
    ]);
});

test('update inactive keeps a site of the run active', function (): void {
    $collectionRunId = CollectionRunId::generate();
    $site = Site::factory()->create([
        'collection_run_id' => $collectionRunId,
        'is_active' => true,
    ]);

    $siteSynchronizer = $this->app->make(SiteSynchronizer::class);
    $siteSynchronizer->updateInactive($collectionRunId);

    $this->assertDatabaseHas(Site::class, [
        'id' => $site->id,
        'is_active' => true,
    ]);
});
