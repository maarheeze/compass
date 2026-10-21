<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Composer\PackageUsages;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\ComposerJson;
use Maarheeze\Compass\Models\ComposerJsonPackage;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the package name with a vendor', function (): void {
    $packageName = sprintf('%s/%s', fake()->slug(), fake()->slug());
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_PACKAGE, $packageName));

    $response->assertViewHas('packageName', $packageName);
});

test('show contains the package usages', function (): void {
    $repository = Repository::factory()->create(['composer_json_oid' => fake()->sha1()]);
    $composerJson = ComposerJson::factory()->create([
        'repository_id' => $repository->id,
        'type' => 'library',
    ]);
    $requiredPackage = ComposerJsonPackage::factory()->create(['composer_json_id' => $composerJson->id]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::GITHUB_PACKAGE, $requiredPackage->name));

    $response->assertViewHas('packageUsages', static function (PackageUsages $packageUsages) use ($requiredPackage): bool {
        return $packageUsages->requiredPackages->sole()->is($requiredPackage);
    });
});
