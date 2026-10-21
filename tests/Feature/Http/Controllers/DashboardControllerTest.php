<?php

declare(strict_types=1);

use Maarheeze\Compass\Analyzer\Alerts;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\Models\Site;
use Maarheeze\Compass\Models\User;
use Maarheeze\Compass\Providers\AuthenticationProvider;

test('show contains the alerts', function (): void {
    $repository = Repository::factory()->create(['last_commit_sha' => fake()->unique()->sha1()]);
    $site = Site::factory()->create([
        'repository_url' => $repository->url,
        'repository_branch' => $repository->default_branch,
        'deployed_commit_hash' => fake()->unique()->sha1(),
        'is_active' => true,
    ]);
    $this->actingAs(User::factory()->create(), AuthenticationProvider::GUARD);

    $response = $this->get(route(RouteName::DASHBOARD));

    $response->assertViewHas('alerts', static function (Alerts $alerts) use ($site): bool {
        return $alerts->sitesBehind->sole()->is($site);
    });
});
