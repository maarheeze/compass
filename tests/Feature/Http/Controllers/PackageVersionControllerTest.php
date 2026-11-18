<?php

declare(strict_types=1);

use Maarheeze\Compass\Collections\ComposerLockPackageCollection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerLock;
use Maarheeze\Compass\Models\ComposerLockPackage;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the version with a slash', function (): void {
    $version = sprintf('dev-%s/%s', fake()->slug(), fake()->slug());
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PACKAGE_VERSION, [
        'packageName' => sprintf('%s/%s', fake()->slug(), fake()->slug()),
        'version' => $version,
    ]));

    $response->assertViewHas('version', $version);
});

test('show contains the locked packages of the version', function (): void {
    $repository = Repository::factory()->create(['composer_json_version' => fake()->sha1()]);
    ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'project',
    ]);
    $lockedPackage = ComposerLockPackage::factory()->create([
        'composer_lock_id' => ComposerLock::factory()->create(['repository_id' => $repository->id])->id,
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::PACKAGE_VERSION, [
        'packageName' => $lockedPackage->name,
        'version' => $lockedPackage->version,
    ]));

    $response->assertViewHas('lockedPackages', static function (ComposerLockPackageCollection $lockedPackages) use ($lockedPackage): bool {
        return $lockedPackages->sole()->is($lockedPackage);
    });
});
