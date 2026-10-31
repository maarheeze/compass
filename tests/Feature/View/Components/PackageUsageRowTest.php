<?php

declare(strict_types=1);

use Maarheeze\Compass\Collector\Composer\PackageSection;
use Maarheeze\Compass\Http\RouteName;
use Maarheeze\Compass\Models\Repository;
use Maarheeze\Compass\View\Components\PackageUsageRow;

test('render renders the package usage row view', function (): void {
    $packageUsageRow = $this->app->make(PackageUsageRow::class, [
        'repository' => Repository::factory()->create(),
        'version' => fake()->semver(),
        'section' => fake()->randomElement(PackageSection::cases()),
    ]);

    expect($packageUsageRow->render()->name())
        ->toBe('compass::components.package-usage-row');
});

test('url is the compass page of the repository', function (): void {
    $repository = Repository::factory()->create();

    $packageUsageRow = $this->app->make(PackageUsageRow::class, [
        'repository' => $repository,
        'version' => fake()->semver(),
        'section' => fake()->randomElement(PackageSection::cases()),
    ]);

    expect($packageUsageRow->url)
        ->toBe(route(RouteName::GITHUB_REPOSITORY, $repository));
});
